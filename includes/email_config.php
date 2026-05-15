<?php
/**
 * Email Configuration for Communications
 */

// Email account configuration
define('EMAIL_IMAP_HOST', 'mail.ascendingpawnchess.com');
define('EMAIL_IMAP_PORT', 993);
define('EMAIL_ADDRESS', 'info@ascendingpawnchess.com');
define('EMAIL_PASSWORD', 'Amerigo20#!');

function buildImapMailboxCandidates($folder = 'INBOX') {
    $base = EMAIL_IMAP_HOST . ':' . EMAIL_IMAP_PORT;
    return [
        '{' . $base . '/imap/ssl/novalidate-cert}' . $folder,
        '{' . $base . '/imap/ssl}' . $folder,
        '{' . $base . '/ssl/novalidate-cert}' . $folder,
        '{' . $base . '/ssl}' . $folder,
        '{' . EMAIL_IMAP_HOST . ':143/imap/notls}' . $folder,
        '{' . EMAIL_IMAP_HOST . ':143/imap}' . $folder,
        '{' . EMAIL_IMAP_HOST . ':143/notls}' . $folder,
        '{' . EMAIL_IMAP_HOST . ':143}' . $folder,
    ];
}

function decodeImapHeaderText($value) {
    if (!is_string($value) || $value === '') {
        return '';
    }

    if (!function_exists('imap_mime_header_decode')) {
        return $value;
    }

    $parts = @imap_mime_header_decode($value);
    if (!is_array($parts)) {
        return $value;
    }

    $decoded = '';
    foreach ($parts as $part) {
        $decoded .= isset($part->text) ? $part->text : '';
    }

    return $decoded !== '' ? $decoded : $value;
}

function fetchImapBodyBySection($imap, $msgno) {
    $candidates = ['1', '1.1', '2', 'text'];
    foreach ($candidates as $section) {
        $body = @imap_fetchbody($imap, $msgno, $section);
        if ($body !== false && $body !== '') {
            return $body;
        }
    }

    $body = @imap_body($imap, $msgno);
    return $body !== false ? $body : '';
}

function decodeImapBody($body, $encoding = null) {
    if ($body === '' || $body === null) {
        return '';
    }

    switch ((int)$encoding) {
        case 3:
            return imap_base64($body);
        case 4:
            return imap_qprint($body);
        default:
            return $body;
    }
}

/**
 * Fetch emails from IMAP mailbox
 * @param string $folder Folder name (default: INBOX)
 * @param int $limit Number of emails to fetch
 * @return array Array of emails
 */
function fetchEmailsFromIMAP($folder = 'INBOX', $limit = 20) {
    // Returns an associative result: ['success' => bool, 'emails' => array, 'error' => string|null]
    $result = ['success' => false, 'emails' => [], 'error' => null, 'debug' => []];

    // Guard: IMAP extension must be enabled in PHP (php_imap)
    if (!function_exists('imap_open')) {
        $result['error'] = 'IMAP extension is not enabled on this server. Please enable php_imap in php.ini and restart Apache.';
        error_log('[IMAP] ' . $result['error']);
        return $result;
    }

    try {
        $attemptErrors = [];
        $imap = null;
        $usedMailbox = null;

        foreach (buildImapMailboxCandidates($folder) as $mailbox) {
            imap_errors();
            imap_alerts();
            $candidate = @imap_open($mailbox, EMAIL_ADDRESS, EMAIL_PASSWORD, OP_READONLY, 1);
            if ($candidate) {
                $imap = $candidate;
                $usedMailbox = $mailbox;
                break;
            }

            $errors = imap_errors();
            $lastError = imap_last_error();
            $attemptErrors[] = [
                'mailbox' => $mailbox,
                'last_error' => $lastError ?: 'unknown',
                'all_errors' => $errors ? array_values($errors) : []
            ];
        }

        if (!$imap) {
            $result['debug'] = $attemptErrors;
            $first = $attemptErrors[0] ?? null;
            $summary = $first ? ($first['last_error'] . ' on ' . $first['mailbox']) : 'Unknown IMAP error';
            error_log('[IMAP] Unable to connect after multiple attempts: ' . json_encode($attemptErrors));
            $result['error'] = 'Unable to connect to mailbox. Tried multiple IMAP connection variants. First error: ' . $summary;
            return $result;
        }

        $result['debug']['mailbox'] = $usedMailbox;

        // Get email count
        $emailCount = imap_num_msg($imap);

        if ($emailCount <= 0) {
            $result['success'] = true;
            $result['emails'] = [];
            imap_close($imap);
            return $result;
        }

        // Fetch latest emails using overview (more reliable) and decode body with fallbacks
        $start = max(1, $emailCount - $limit + 1);
        for ($msgno = $emailCount; $msgno >= $start; $msgno--) {
            // Use overview for metadata (includes uid, seen flag, date, subject, from)
            $overviews = @imap_fetch_overview($imap, (string)$msgno, 0);
            if (!$overviews || !isset($overviews[0])) continue;
            $ov = $overviews[0];

            // Determine UID and seen state
            $uid = isset($ov->uid) ? intval($ov->uid) : imap_uid($imap, $msgno);
            $is_read = isset($ov->seen) ? (bool)$ov->seen : (isset($ov->recent) ? !$ov->recent : false);

            // Normalize from and subject
            $fromRaw = isset($ov->from) ? decodeImapHeaderText($ov->from) : '';
            $subjectRaw = isset($ov->subject) ? $ov->subject : '(No Subject)';
            $subject = decodeImapHeaderText($subjectRaw);

            // Date normalization
            $dateStr = isset($ov->date) ? $ov->date : null;
            $timestamp = $dateStr ? strtotime($dateStr) : false;
            $dateFormatted = $timestamp ? date('Y-m-d H:i:s', $timestamp) : ($dateStr ?: date('Y-m-d H:i:s'));

            // Fetch body: try section 1, then 1.1, then full body
            $body = '';
            // inspect structure to decide decoding
            $structure = @imap_fetchstructure($imap, $msgno);
            $encoding = null;
            if ($structure && isset($structure->parts) && is_array($structure->parts) && count($structure->parts) > 0) {
                // prefer first text/plain part
                foreach ($structure->parts as $pIndex => $part) {
                    $partno = $pIndex + 1;
                    if (isset($part->subtype) && in_array(strtolower($part->subtype), ['plain', 'html'])) {
                        $raw = @imap_fetchbody($imap, $msgno, (string)$partno);
                        $encoding = isset($part->encoding) ? $part->encoding : null;
                        if ($raw !== false && $raw !== '') { $body = $raw; break; }
                    }
                }
            }

            if ($body === '') {
                $body = fetchImapBodyBySection($imap, $msgno);
            }

            $body = decodeImapBody($body, $encoding);
            if ($body !== '' && preg_match('/=\?[A-Za-z0-9_-]+\?[BbQq]\?/i', $body)) {
                $body = quoted_printable_decode($body);
            }

            // extract plain text snippet
            $snippet = substr(strip_tags($body), 0, 300);

            $result['emails'][] = [
                'uid' => $uid,
                'msgno' => $msgno,
                'from' => $fromRaw,
                'from_name' => '',
                'subject' => $subject,
                'date' => $dateFormatted,
                'body' => $snippet,
                'is_read' => $is_read
            ];
        }

        imap_close($imap);
        $result['success'] = true;
    } catch (Exception $e) {
        $result['error'] = 'IMAP exception: ' . $e->getMessage();
        error_log('[IMAP] Exception fetching emails: ' . $e->getMessage());
    }

    return $result;
}

/**
 * Mark email as read in IMAP
 * @param int $uid Email UID
 */
function markEmailAsRead($uid) {
    // Guard: IMAP extension must be enabled
    if (!function_exists('imap_open')) {
        error_log('[IMAP] markEmailAsRead called but IMAP extension is not available.');
        return false;
    }

    try {
        $mailbox = '{' . EMAIL_IMAP_HOST . ':' . EMAIL_IMAP_PORT . '/imap/ssl}INBOX';
        $imap = @imap_open($mailbox, EMAIL_ADDRESS, EMAIL_PASSWORD);
        if (!$imap) {
            $imapErr = imap_last_error();
            error_log('[IMAP] markEmailAsRead: unable to open mailbox: ' . ($imapErr ?: 'unknown'));
            return false;
        }

        $success = imap_setflag_full($imap, $uid, "\\Seen");
        imap_close($imap);
        return (bool)$success;
    } catch (Exception $e) {
        error_log('[IMAP] markEmailAsRead exception: ' . $e->getMessage());
    }

    return false;
}
?>
