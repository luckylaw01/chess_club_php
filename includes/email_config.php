<?php
/**
 * Email Configuration for Communications
 */

// Email account configuration
define('EMAIL_IMAP_HOST', 'mail.ascendingpawnchess.com');
define('EMAIL_IMAP_PORT', 993);
define('EMAIL_ADDRESS', 'info@ascendingpawnchess.com');
define('EMAIL_PASSWORD', 'fakepassword2');

/**
 * Fetch emails from IMAP mailbox
 * @param string $folder Folder name (default: INBOX)
 * @param int $limit Number of emails to fetch
 * @return array Array of emails
 */
function fetchEmailsFromIMAP($folder = 'INBOX', $limit = 20) {
    $emails = [];
    
    try {
        // Connect to IMAP server with SSL
        $imap = imap_open(
            '{' . EMAIL_IMAP_HOST . ':' . EMAIL_IMAP_PORT . '/imap/ssl}' . $folder,
            EMAIL_ADDRESS,
            EMAIL_PASSWORD
        );
        
        if (!$imap) {
            error_log('IMAP Connection Error: ' . imap_last_error());
            return [];
        }
        
        // Get email count
        $emailCount = imap_num_msg($imap);
        
        // Fetch latest emails
        $start = max(1, $emailCount - $limit + 1);
        for ($i = $emailCount; $i >= $start; $i--) {
            $header = imap_headerinfo($imap, $i);
            $body = imap_fetchbody($imap, $i, 1);
            
            // Decode if necessary
            if ($header->encoding == 3) { // BASE64
                $body = imap_base64($body);
            } elseif ($header->encoding == 1) { // 7BIT
                $body = $body;
            }
            
            $emails[] = [
                'uid' => $i,
                'from' => isset($header->from[0]->mailbox) ? $header->from[0]->mailbox . '@' . $header->from[0]->host : 'Unknown',
                'from_name' => isset($header->from[0]->personal) ? $header->from[0]->personal : '',
                'subject' => $header->subject ?? '(No Subject)',
                'date' => $header->date ?? date('Y-m-d H:i:s'),
                'body' => substr(strip_tags($body), 0, 200),
                'is_read' => !($header->Unseen == 'U')
            ];
        }
        
        imap_close($imap);
    } catch (Exception $e) {
        error_log('IMAP Error: ' . $e->getMessage());
    }
    
    return $emails;
}

/**
 * Mark email as read in IMAP
 * @param int $uid Email UID
 */
function markEmailAsRead($uid) {
    try {
        $imap = imap_open(
            '{' . EMAIL_IMAP_HOST . ':' . EMAIL_IMAP_PORT . '/imap/ssl}INBOX',
            EMAIL_ADDRESS,
            EMAIL_PASSWORD
        );
        
        if ($imap) {
            imap_setflag_full($imap, $uid, "\\Seen");
            imap_close($imap);
            return true;
        }
    } catch (Exception $e) {
        error_log('IMAP Mark as Read Error: ' . $e->getMessage());
    }
    
    return false;
}
?>
