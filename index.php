<?php
session_start();
$pageTitle = "Home";
include 'includes/header.php';
require_once 'includes/home_images.php';
require_once 'includes/db_connect.php';

// Fetch all available plans
$plans = [];
if (isset($conn)) {
    $sql_plans = "SELECT * FROM membership_plans";
    if ($result_plans = $conn->query($sql_plans)) {
        while ($row = $result_plans->fetch_assoc()) {
            $plans[] = $row;
        }
    }
}
?>
    <!-- Hero Gallery Slideshow Styles -->
    <style>
        /* ===== HERO SLIDESHOW ENGINE ===== */
        .hero-slideshow { position: relative; width: 100%; height: 100vh; min-height: 700px; overflow: hidden; }

        /* Base chess background layer */
        .hero-base-bg { position: absolute; inset: 0; z-index: 0; }
        .hero-base-bg img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
        .hero-base-bg::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(43,43,43,0.85) 50%, rgba(0,51,0,0.6) 100%);
        }

        /* Slide container */
        .hero-slides { position: absolute; inset: 0; z-index: 1; }

        /* Individual slide */
        .hero-slide {
            position: absolute; inset: 0;
            opacity: 0;
            transform: scale(1.15);
            transition: opacity 1.8s cubic-bezier(0.4, 0, 0.2, 1), transform 8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: opacity, transform;
        }
        .hero-slide.active {
            opacity: 1;
            transform: scale(1);
        }
        .hero-slide.exiting {
            opacity: 0;
            transform: scale(0.97);
            transition: opacity 1.4s cubic-bezier(0.4, 0, 0.2, 1), transform 1.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hero-slide img {
            width: 100%; height: 100%; object-fit: cover;
            animation: heroKenBurns 12s ease-in-out infinite alternate;
        }
        .hero-slide.active img { animation-play-state: running; }
        .hero-slide:not(.active) img { animation-play-state: paused; }

        /* Ken Burns zoom & pan variants */
        @keyframes heroKenBurns {
            0%   { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.12) translate(-1.5%, -1%); }
        }
        .hero-slide:nth-child(2n) img { animation-name: heroKenBurns2; }
        @keyframes heroKenBurns2 {
            0%   { transform: scale(1.08) translate(1%, 0.5%); }
            100% { transform: scale(1) translate(-0.5%, -0.5%); }
        }
        .hero-slide:nth-child(3n) img { animation-name: heroKenBurns3; }
        @keyframes heroKenBurns3 {
            0%   { transform: scale(1) translate(0.5%, 1%); }
            100% { transform: scale(1.1) translate(-1%, 0.5%); }
        }

        /* Gradient vignette over slides */
        .hero-slide::after {
            content: ''; position: absolute; inset: 0; z-index: 1;
            background:
                radial-gradient(ellipse at 20% 80%, rgba(128,210,0,0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(255,165,0,0.1) 0%, transparent 60%),
                linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 40%, rgba(0,0,0,0.05) 70%, rgba(0,0,0,0.3) 100%);
            pointer-events: none;
        }

        /* Slide caption */
        .hero-slide-caption {
            position: absolute; bottom: 140px; left: 50%; transform: translateX(-50%) translateY(30px);
            z-index: 5; opacity: 0;
            transition: opacity 0.8s ease 0.6s, transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.6s;
            text-align: center;
        }
        .hero-slide.active .hero-slide-caption {
            opacity: 1; transform: translateX(-50%) translateY(0);
        }

        /* Content overlay */
        .hero-content-overlay {
            position: absolute; inset: 0; z-index: 10;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem; pointer-events: none;
        }
        .hero-content-overlay > * { pointer-events: auto; }

        /* Floating content (no background card) */
        .hero-float-content {
            text-align: center; max-width: 700px; width: 100%;
            animation: heroCardEntrance 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }
        .hero-float-content h1 {
            text-shadow: 0 4px 30px rgba(0,0,0,0.5), 0 1px 3px rgba(0,0,0,0.4);
        }
        @keyframes heroCardEntrance {
            from { opacity: 0; transform: translateY(40px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Navigation dots */
        .hero-dots {
            position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
            z-index: 20; display: flex; gap: 12px; align-items: center;
        }
        .hero-dot {
            width: 12px; height: 12px; border-radius: 50%;
            background: rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.5);
            cursor: pointer; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden;
        }
        .hero-dot:hover { background: rgba(255,255,255,0.6); transform: scale(1.2); }
        .hero-dot.active {
            width: 44px; border-radius: 20px;
            background: #80D200; border-color: #80D200;
            box-shadow: 0 0 20px rgba(128,210,0,0.5);
        }
        .hero-dot.active::after {
            content: ''; position: absolute; top: 0; left: 0;
            height: 100%; width: 0%; background: rgba(255,255,255,0.35); border-radius: 20px;
            animation: dotProgress 5s linear forwards;
        }
        @keyframes dotProgress {
            from { width: 0%; } to { width: 100%; }
        }

        /* Prev / Next arrows */
        .hero-arrow {
            position: absolute; top: 50%; z-index: 20;
            width: 52px; height: 52px; border-radius: 50%;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            color: white; font-size: 18px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; opacity: 0; transform: translateY(-50%);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .hero-slideshow:hover .hero-arrow { opacity: 1; }
        .hero-arrow:hover { background: rgba(128,210,0,0.6); border-color: rgba(128,210,0,0.8); transform: translateY(-50%) scale(1.1); }
        .hero-arrow-prev { left: 24px; }
        .hero-arrow-next { right: 24px; }

        /* Floating particle accents */
        .hero-particle {
            position: absolute; border-radius: 50%; pointer-events: none; z-index: 2;
            animation: floatParticle 20s ease-in-out infinite;
        }
        @keyframes floatParticle {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.3; }
            25% { transform: translate(30px, -40px) scale(1.2); opacity: 0.5; }
            50% { transform: translate(-20px, -60px) scale(0.8); opacity: 0.2; }
            75% { transform: translate(40px, -20px) scale(1.1); opacity: 0.4; }
        }

        /* Slide counter */
        .hero-counter {
            position: absolute; top: 40px; right: 40px; z-index: 20;
            font-family: inherit; color: rgba(255,255,255,0.5);
            font-size: 13px; font-weight: 800; letter-spacing: 0.15em;
            text-transform: uppercase;
        }
        .hero-counter .current { color: #80D200; font-size: 28px; }

        @media (max-width: 768px) {
            .hero-glass-card { padding: 2rem 1.5rem; border-radius: 28px; margin-top: 80px; }
            .hero-arrow { opacity: 0.8; transform: translateY(-50%) scale(0.8); }
            .hero-arrow-prev { left: 8px; }
            .hero-arrow-next { right: 8px; }
            .hero-counter { top: 100px; right: 24px; }
            .hero-slide-caption { bottom: 100px; }
        }
    </style>

    <!-- Hero Section: Cinematic Gallery Slideshow -->
    <section class="hero-slideshow" id="heroSlideshow">

        <!-- Base chess background layer -->
        <div class="hero-base-bg">
            <img src="<?php echo htmlspecialchars(get_home_image('hero_background')); ?>"
                 class="<?php if(is_admin_user()) echo 'admin-editable'; ?>" data-image-key="hero_background"
                 alt="Chess Background">
        </div>

        <!-- Floating particles -->
        <div class="hero-particle" style="width:6px;height:6px;background:#80D200;top:20%;left:10%;animation-delay:0s;"></div>
        <div class="hero-particle" style="width:4px;height:4px;background:#FFA500;top:60%;left:85%;animation-delay:5s;"></div>
        <div class="hero-particle" style="width:8px;height:8px;background:#80D200;top:75%;left:30%;animation-delay:10s;"></div>
        <div class="hero-particle" style="width:5px;height:5px;background:#FFCC66;top:15%;left:70%;animation-delay:15s;"></div>
        <div class="hero-particle" style="width:3px;height:3px;background:#80D200;top:45%;left:50%;animation-delay:3s;"></div>

        <!-- Gallery Slides -->
        <div class="hero-slides" id="heroSlides">
            <?php
            $slideData = get_gallery_images();
            foreach ($slideData as $idx => $slide):
            ?>
            <div class="hero-slide<?php echo $idx === 0 ? ' active' : ''; ?>" data-index="<?php echo $idx; ?>">
                <img src="<?php echo htmlspecialchars($slide['image']); ?>"
                     alt="<?php echo htmlspecialchars($slide['alt']); ?>"
                     class="<?php if(is_admin_user()) echo 'admin-editable'; ?>"
                     data-image-key="gallery_dynamic_<?php echo $idx; ?>">
                <div class="hero-slide-caption">
                    <span class="inline-block px-5 py-2 rounded-full text-white text-[11px] font-black uppercase tracking-[0.2em]"
                          style="background:rgba(128,210,0,0.35);backdrop-filter:blur(8px);border:1px solid rgba(128,210,0,0.4);">
                        <i class="fas fa-camera mr-2 text-[9px]"></i><?php echo htmlspecialchars($slide['caption']); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Floating content overlay (no background) -->
        <div class="hero-content-overlay">
            <div class="hero-float-content">
                <h1 class="text-5xl lg:text-7xl font-extrabold leading-[1.05] tracking-tight text-white mb-10">
                    Welcome to<br>
                    <span style="color:#80D200;">Ascending Pawn</span> Chess
                </h1>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="<?php echo isset($_SESSION['id']) ? 'club.php' : 'register.php'; ?>"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-10 py-5 bg-white/10 text-white font-bold rounded-full hover:bg-white hover:text-slate-900 hover:scale-105 active:scale-95 transition-all uppercase text-sm tracking-wider"
                       style="border:1px solid rgba(255,255,255,0.3);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
                        Start Your Journey <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <button id="openGalleryBtn" type="button"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-10 py-5 bg-transparent text-white font-bold rounded-full hover:bg-white/10 hover:scale-105 active:scale-95 transition-all uppercase text-sm tracking-wider"
                       style="border:1px solid rgba(255,255,255,0.6);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
                        <i class="fas fa-images"></i> View Gallery
                    </button>
                </div>
            </div>
        </div>



        <!-- Navigation arrows -->
        <button class="hero-arrow hero-arrow-prev" id="heroPrev" aria-label="Previous slide">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hero-arrow hero-arrow-next" id="heroNext" aria-label="Next slide">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Dot indicators -->
        <div class="hero-dots" id="heroDots">
            <?php foreach ($slideData as $idx => $slide): ?>
            <button class="hero-dot<?php echo $idx === 0 ? ' active' : ''; ?>" data-index="<?php echo $idx; ?>" aria-label="Go to slide <?php echo $idx + 1; ?>"></button>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Modal -->
        <div id="galleryModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/95 backdrop-blur-sm opacity-0 transition-opacity duration-500">
            <button id="closeGallery" class="absolute top-6 right-6 text-white/70 hover:text-white text-4xl z-[110] transition-colors"><i class="fas fa-times"></i></button>
            
            <div class="relative w-full max-w-6xl mx-auto h-[80vh] flex items-center justify-center px-4">
                <button id="modalPrev" class="absolute left-4 md:left-0 text-white/50 hover:text-white text-5xl z-50 transition-colors"><i class="fas fa-chevron-left"></i></button>
                
                <div class="relative max-h-full max-w-full flex flex-col items-center justify-center">
                    <img id="modalImage" src="" class="max-h-[70vh] max-w-full object-contain rounded-xl shadow-2xl transition-opacity duration-300" alt="Gallery View">
                    <div id="modalCaption" class="mt-6 text-center text-white/80 font-medium tracking-wide text-lg uppercase tracking-[0.2em]"></div>
                </div>
                
                <button id="modalNext" class="absolute right-4 md:right-0 text-white/50 hover:text-white text-5xl z-50 transition-colors"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

    </section>

    <!-- Hero Slideshow Engine -->
    <script>
    (function() {
        const slides = document.querySelectorAll('.hero-slide');
        const dots   = document.querySelectorAll('.hero-dot');
        const total  = slides.length;
        let current  = 0;
        let timer    = null;
        let paused   = false;
        const INTERVAL = 5000;

        const countCurrent = document.getElementById('heroCountCurrent');
        const countTotal   = document.getElementById('heroCountTotal');
        if (countTotal) countTotal.textContent = String(total).padStart(2, '0');

        function goTo(index, direction) {
            if (index === current) return;
            const prev = current;
            current = ((index % total) + total) % total;

            // Animate out old slide
            slides[prev].classList.remove('active');
            slides[prev].classList.add('exiting');
            setTimeout(() => slides[prev].classList.remove('exiting'), 1600);

            // Activate new slide
            slides[current].classList.add('active');

            // Update dots
            dots.forEach((d, i) => {
                d.classList.toggle('active', i === current);
                // Restart progress animation
                if (i === current) {
                    d.style.animation = 'none';
                    d.offsetHeight; // force reflow
                    d.style.animation = '';
                }
            });

            // Update counter
            if (countCurrent) countCurrent.textContent = String(current + 1).padStart(2, '0');

            resetTimer();
        }

        function next() { goTo(current + 1, 'next'); }
        function prev() { goTo(current - 1, 'prev'); }

        function resetTimer() {
            clearInterval(timer);
            if (!paused) timer = setInterval(next, INTERVAL);
        }

        // Dot clicks
        dots.forEach(dot => {
            dot.addEventListener('click', () => goTo(parseInt(dot.dataset.index), 'next'));
        });

        // Arrow clicks
        document.getElementById('heroPrev')?.addEventListener('click', prev);
        document.getElementById('heroNext')?.addEventListener('click', next);

        // Pause on hover
        const container = document.getElementById('heroSlideshow');
        container?.addEventListener('mouseenter', () => { paused = true; clearInterval(timer); });
        container?.addEventListener('mouseleave', () => { paused = false; resetTimer(); });

        // Touch swipe support
        let touchStartX = 0;
        container?.addEventListener('touchstart', (e) => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
        container?.addEventListener('touchend', (e) => {
            const diff = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(diff) > 50) diff > 0 ? prev() : next();
        }, { passive: true });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!modal.classList.contains('hidden')) {
                // Modal is open, handle keys for modal
                if (e.key === 'ArrowLeft') document.getElementById('modalPrev')?.click();
                if (e.key === 'ArrowRight') document.getElementById('modalNext')?.click();
                if (e.key === 'Escape') closeModal();
                return;
            }

            const rect = container?.getBoundingClientRect();
            if (!rect || rect.bottom < 0 || rect.top > window.innerHeight) return;
            if (e.key === 'ArrowLeft') prev();
            if (e.key === 'ArrowRight') next();
        });

        // Start auto-play
        timer = setInterval(next, INTERVAL);

        // --- Gallery Modal Logic ---
        const modal = document.getElementById('galleryModal');
        const closeGalleryBtn = document.getElementById('closeGallery');
        const openGalleryBtn = document.getElementById('openGalleryBtn');
        const modalImage = document.getElementById('modalImage');
        const modalCaption = document.getElementById('modalCaption');
        const modalPrev = document.getElementById('modalPrev');
        const modalNext = document.getElementById('modalNext');
        
        let modalIndex = 0;
        
        function updateModalImage(idx) {
            modalImage.style.opacity = '0';
            setTimeout(() => {
                modalImage.src = slides[idx].querySelector('img').src;
                // Clean up the caption text (remove the camera icon if present)
                let captionText = slides[idx].querySelector('.hero-slide-caption span').textContent.trim();
                modalCaption.textContent = captionText;
                modalImage.style.opacity = '1';
            }, 150);
        }

        function openModal() {
            modalIndex = current; // Start from current hero slide
            updateModalImage(modalIndex);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // small delay to allow display to apply before transition
            setTimeout(() => modal.classList.remove('opacity-0'), 10);
            paused = true; // pause hero auto-play
            clearInterval(timer);
            document.body.style.overflow = 'hidden'; // prevent background scrolling
        }

        function closeModal() {
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                paused = false;
                resetTimer();
                document.body.style.overflow = '';
            }, 500);
        }

        openGalleryBtn?.addEventListener('click', openModal);
        closeGalleryBtn?.addEventListener('click', closeModal);
        
        modalPrev?.addEventListener('click', () => {
            modalIndex = (modalIndex - 1 + total) % total;
            updateModalImage(modalIndex);
        });
        
        modalNext?.addEventListener('click', () => {
            modalIndex = (modalIndex + 1) % total;
            updateModalImage(modalIndex);
        });
        
        // Close modal on outside click
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    })();
    </script>

    <!-- Section 2: Date of Events (Events Calendar Section) -->
    <section class="py-24 px-6 bg-slate-50 dark:bg-darkBg transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6 animate-slide-up">
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandOrange/10 dark:bg-brandOrange/20 text-brandOrange dark:text-brandOrange text-xs font-bold uppercase tracking-widest mb-4">
                        <i class="fas fa-trophy text-[10px]"></i> Action on the board
                    </div>
                    <h2 class="text-3xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-2">Events Calendar</h2>
                    <p class="text-slate-500 max-w-xl">Register for our upcoming tournaments and local chess meets. Ready your strategy.</p>
                </div>
                <a href="tournaments.php" class="text-brandGreen font-bold uppercase tracking-widest text-xs hover:underline decoration-2 underline-offset-8 transition-all flex items-center gap-2">
                    View All Tournaments <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                $calQuery = "SELECT * FROM tournaments 
                             ORDER BY 
                                 CASE WHEN status = 'ongoing' THEN 1 
                                      WHEN status = 'upcoming' THEN 2 
                                      ELSE 3 END, 
                                 event_date ASC 
                             LIMIT 3";
                $calResult = $conn->query($calQuery);
                if ($calResult && $calResult->num_rows > 0):
                    while ($t = $calResult->fetch_assoc()):
                        $eventDate = strtotime($t['event_date']);
                        $day = date('d', $eventDate);
                        $month = date('M', $eventDate);
                        $title = htmlspecialchars($t['title']);
                        $time = date('h:i A', $eventDate);
                        $location = htmlspecialchars($t['location']);
                        
                        // Determine icon (video if online, map marker otherwise)
                        $locIcon = 'fa-map-marker-alt';
                        if (preg_match('/online|zoom|virtual|interactive/i', $location)) {
                            $locIcon = 'fa-video';
                        }
                        
                        if ($t['status'] === 'ongoing') {
                            $dateBg = 'bg-brandOrange text-white';
                            $btnText = 'Join';
                            $btnClass = 'border-brandOrange/20 text-brandOrange hover:bg-brandOrange hover:text-white';
                            $actionHtml = '<a href="tournaments.php" class="w-full mt-6 py-3 border ' . $btnClass . ' text-xs font-bold rounded-xl transition-all uppercase tracking-widest text-center block">' . $btnText . '</a>';
                        } elseif ($t['status'] === 'upcoming') {
                            $dateBg = 'bg-brandGreen text-white';
                            $btnText = 'Register';
                            $btnClass = 'border-brandGreen/20 text-brandGreen hover:bg-brandGreen hover:text-white';
                            $actionHtml = '<a href="tournaments.php" class="w-full mt-6 py-3 border ' . $btnClass . ' text-xs font-bold rounded-xl transition-all uppercase tracking-widest text-center block">' . $btnText . '</a>';
                        } else {
                            $dateBg = 'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400';
                            $statusLabel = $t['status'] === 'completed' ? 'Finished' : ucfirst($t['status']);
                            $actionHtml = '<span class="w-full mt-6 py-3 text-slate-400 text-xs font-bold uppercase tracking-widest text-center block bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800">' . $statusLabel . '</span>';
                        }
                ?>
                        <div class="group bg-white dark:bg-slate-900/50 p-8 rounded-[36px] border border-slate-200 dark:border-slate-800 hover:border-brandGreen/40 hover:shadow-2xl transition-all duration-300 flex flex-col justify-between glass shadow-lg">
                            <div>
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex flex-col items-center justify-center w-14 h-14 <?php echo $dateBg; ?> rounded-2xl font-black">
                                        <span class="text-xl leading-none"><?php echo $day; ?></span>
                                        <span class="text-[9px] uppercase tracking-widest leading-none mt-1"><?php echo $month; ?></span>
                                    </div>
                                    <span class="px-3.5 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest <?php echo $t['status'] === 'ongoing' ? 'bg-brandOrange/10 text-brandOrange' : ($t['status'] === 'upcoming' ? 'bg-brandGreen/10 text-brandGreen' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'); ?>">
                                        <?php echo ucfirst($t['status']); ?>
                                    </span>
                                </div>
                                <h3 class="text-xl font-bold uppercase tracking-tight text-slate-900 dark:text-white mb-4 group-hover:text-brandGreen transition-colors line-clamp-1">
                                    <?php echo $title; ?>
                                </h3>
                                <div class="space-y-2 text-xs text-slate-500 mb-2">
                                    <p class="flex items-center gap-2"><i class="far fa-clock text-brandGreen"></i> <?php echo $time; ?></p>
                                    <p class="flex items-center gap-2"><i class="fas <?php echo $locIcon; ?> text-brandGreen"></i> <?php echo $location; ?></p>
                                </div>
                            </div>
                            <?php echo $actionHtml; ?>
                        </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="col-span-3 text-center py-10 bg-white dark:bg-slate-900/40 rounded-3xl border border-slate-100 dark:border-slate-800">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">No events scheduled at this moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section 3: Join the Membership Section -->
    <section class="py-24 px-6 bg-white dark:bg-black transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 animate-slide-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGreen/10 dark:bg-brandGreen/20 text-brandGreen dark:text-brandGreen text-xs font-bold uppercase tracking-widest mb-4">
                    <i class="fas fa-calendar-alt text-[10px]"></i> Get Exclusive Access
                </div>
                <h2 class="text-3xl lg:text-5xl font-extrabold tracking-tight mb-4 text-slate-900 dark:text-white">Join the Membership</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">
                    Choose the plan that fits your ambition. Get access to rating tournaments, weekly chess nights, coaching materials, and our community.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php if (!empty($plans)): ?>
                    <?php foreach($plans as $plan): ?>
                        <div class="p-8 rounded-[40px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl flex flex-col hover:border-brandGreen/40 hover:shadow-2xl transition-all duration-300 relative group">
                            <h3 class="text-2xl font-bold mb-4 text-slate-900 dark:text-white"><?php echo htmlspecialchars($plan['name']); ?></h3>
                            <p class="text-slate-500 mb-6 font-medium text-sm"><?php echo htmlspecialchars($plan['description']); ?></p>
                            <div class="text-4xl font-black mb-8 text-slate-900 dark:text-white">KES <?php echo number_format($plan['price']); ?><span
                                    class="text-sm font-bold text-slate-400">/<?php echo $plan['duration_months']; ?> mo</span></div>
                            <ul class="space-y-4 mb-8 text-sm font-medium flex-grow text-slate-600 dark:text-slate-300">
                                <?php 
                                $features = explode(',', $plan['features']);
                                foreach($features as $feature):
                                ?>
                                    <li><i class="fas fa-check text-brandGreen mr-2"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true ? 'club.php' : 'register.php'; ?>"
                                class="w-full py-4 text-center bg-brandGreen hover:bg-brandGreen/90 text-white rounded-2xl font-bold uppercase tracking-widest transition-all hover:scale-105 block shadow-lg">
                                Select Plan
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-3 text-center p-8 bg-white dark:bg-slate-900/50 rounded-[32px] border border-slate-200 dark:border-slate-800">
                        <p class="font-bold text-slate-600 dark:text-slate-300">No membership plans available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section 4: Elite Members Section -->
    <section class="py-24 px-6 bg-slate-50 dark:bg-darkBg transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6 animate-slide-up">
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brandGold/10 dark:bg-brandGold/20 text-brandGold dark:text-brandGold text-xs font-bold uppercase tracking-widest mb-4">
                        <i class="fas fa-crown text-[10px]"></i> Hall of Champions
                    </div>
                    <h2 class="text-3xl lg:text-5xl font-extrabold tracking-tight mb-4 text-slate-900 dark:text-white">Elite Members</h2>
                    <p class="text-slate-500 max-w-xl">Meet the grandmasters and rising stars of our community. Every pawn has the potential to become a queen.</p>
                </div>
                <a href="club.php" class="text-brandGreen font-bold uppercase tracking-widest text-xs hover:underline decoration-2 underline-offset-8 transition-all">View Leaderboard <i class="fas fa-external-link-alt ml-2"></i></a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                // Fetch top 3 members by elo_rating
                $topMembers = [];
                if (isset($conn)) {
                    $sql = "SELECT id, username, full_name, elo_rating, profile_picture FROM users WHERE role IN ('user','coach') AND membership_status = 'active' AND elo_rating IS NOT NULL ORDER BY elo_rating DESC LIMIT 3";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $topMembers = $res->fetch_all(MYSQLI_ASSOC);
                        $stmt->close();
                    }
                }

                $accentClasses = ['brandGreen', 'brandGold', 'brandBrown'];
                if (!empty($topMembers)):
                    $i = 0;
                    foreach ($topMembers as $m):
                        $initial = '';
                        $name = !empty($m['full_name']) ? $m['full_name'] : $m['username'];
                        if (!empty($m['full_name'])) {
                            $initial = mb_strtoupper(mb_substr($m['full_name'], 0, 1));
                        } else {
                            $initial = mb_strtoupper(mb_substr($m['username'], 0, 1));
                        }
                        $rating = !empty($m['elo_rating']) ? intval($m['elo_rating']) : 1200;
                        $profile = !empty($m['profile_picture']) ? $m['profile_picture'] : '';
                        $hasPic = $profile && file_exists(__DIR__ . '/' . $profile);
                        $accent = $accentClasses[$i % count($accentClasses)];
                ?>
                <div class="bg-white dark:bg-slate-900/50 p-6 rounded-[32px] border border-slate-200 dark:border-slate-800 hover:border-brandGreen/40 hover:shadow-2xl transition-all duration-300 group glass shadow-md text-center">
                    <div class="relative w-20 h-20 mb-6 mx-auto">
                        <?php if ($hasPic): ?>
                            <img src="<?php echo htmlspecialchars($profile); ?>" alt="<?php echo htmlspecialchars($name); ?>" class="w-full h-full object-cover rounded-2xl">
                        <?php else: ?>
                            <div class="w-full h-full rounded-2xl bg-<?php echo $accent; ?>/20 flex items-center justify-center text-<?php echo $accent; ?> text-3xl font-black"><?php echo htmlspecialchars($initial); ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-black text-lg uppercase tracking-tight text-slate-900 dark:text-white mb-1"><?php echo htmlspecialchars($name); ?></h3>
                        <p class="text-[10px] font-bold text-<?php echo $accent; ?> uppercase tracking-widest mb-4">Rating: <?php echo $rating; ?></p>
                        <p class="text-xs text-slate-500 italic mb-6">Top player in our community.</p>
                        <div class="flex justify-center gap-2">
                            <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-[9px] font-bold text-slate-500 uppercase">Top Player</span>
                        </div>
                    </div>
                </div>
                <?php
                        $i++;
                    endforeach;
                else:
                ?>
                <div class="col-span-3 text-center p-8 bg-white dark:bg-slate-900/50 rounded-[32px] border border-slate-200 dark:border-slate-800">
                    <p class="font-bold text-slate-600 dark:text-slate-300">No members found yet. Be the first to join!</p>
                </div>
                <?php endif; ?>

                <!-- Add Personal Profile Link -->
                <a href="register.php" class="bg-brandGreen/5 border-2 border-dashed border-brandGreen/30 p-6 rounded-[32px] flex flex-col items-center justify-center text-center group cursor-pointer hover:bg-brandGreen/10 hover:border-solid transition-all shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-brandGreen text-white flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h3 class="font-bold text-sm uppercase tracking-widest text-brandGreen">Join the Ranks</h3>
                    <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold tracking-tighter">Create your profile</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Section 5: Rest of the sections -->

    <!-- Features Section -->
    <section class="py-24 px-6 bg-white dark:bg-black transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20 animate-slide-up">
                <h2 class="text-3xl lg:text-5xl font-bold mb-4 text-slate-900 dark:text-white">Excellence in Every Move</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Our holistic approach combines tradition with modern technology to deliver the best chess experience.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div
                    class="group p-8 rounded-[32px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                    <div
                        class="w-14 h-14 bg-brandGreen/10 dark:bg-brandGreen/20 rounded-2xl flex items-center justify-center text-brandGreen mb-6 transition-transform group-hover:scale-110">
                        <i class="fas fa-bullseye text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 dark:text-white">Structured Learning</h3>
                    <p class="text-slate-500 text-base leading-relaxed mb-6">Curriculum-based coaching designed for Beginners and Advanced players with progress tracking.</p>
                    <a href="coaching_levels.php" class="inline-flex items-center text-brandGreen font-bold text-sm hover:underline">Explore Levels <i
                            class="fas fa-arrow-right ml-1"></i></a>
                </div>

                <div
                    class="group p-8 rounded-[32px] bg-brandGreen/70 text-white shadow-xl shadow-brandGreen/20 hover:scale-[1.02] transition-all duration-500">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white mb-6">
                        <i class="fas fa-globe text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Hybrid Academy</h3>
                    <p class="text-white/80 text-base leading-relaxed mb-6">Attend in-person sessions at our Nyeri hub or join global interactive online classes.</p>
                    <a href="coaching_schedule.php" class="inline-flex items-center text-white font-bold text-sm hover:underline">See Schedule <i
                            class="fas fa-arrow-right ml-1"></i></a>
                </div>

                <div
                    class="group p-8 rounded-[32px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
                    <div
                        class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center text-purple-600 mb-6 transition-transform group-hover:scale-110">
                        <i class="fas fa-user-shield text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 dark:text-white">Certified Coaching</h3>
                    <p class="text-slate-500 text-base leading-relaxed mb-6">Learn from FIDE instructors and national league players with a proven track record.</p>
                    <a href="coaching_trainers.php" class="inline-flex items-center text-purple-600 font-bold text-sm hover:underline">Meet Trainers <i
                            class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Learning Lab & News Section -->
    <section class="py-24 px-6 bg-slate-50 dark:bg-darkBg transition-colors duration-500">
        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Learning Resources -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brandGold/10 flex items-center justify-center text-brandGold">
                            <i class="fas fa-graduation-cap text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold uppercase tracking-tight text-slate-900 dark:text-white">Learning Lab</h2>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="p-6 bg-brandGreen/5 dark:bg-brandGreen/10 rounded-3xl border border-brandGreen/10 hover:border-brandGreen/30 hover:shadow-xl transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-brandGreen text-white flex items-center justify-center mb-4">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <h4 class="font-bold text-sm mb-2 group-hover:text-brandGreen transition-colors text-slate-900 dark:text-white">Daily Puzzles</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mb-4">Sharpen your tactics with our curated daily chess puzzles.</p>
                            <a href="daily_puzzles.php" class="text-[10px] font-bold uppercase tracking-widest text-brandGreen flex items-center gap-2">Solve Now <i class="fas fa-arrow-right text-[8px]"></i></a>
                        </div>

                        <div class="p-6 bg-brandGold/5 dark:bg-brandGold/10 rounded-3xl border border-brandGold/10 hover:border-brandGold/30 hover:shadow-xl transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-brandGold text-white flex items-center justify-center mb-4">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <h4 class="font-bold text-sm mb-2 group-hover:text-brandGold transition-colors text-slate-900 dark:text-white">Strategy Guides</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mb-4">Comprehensive guides from opening theory to endgame mastery.</p>
                            <a href="strategy_guides.php" class="text-[10px] font-bold uppercase tracking-widest text-brandGold flex items-center gap-2">Browse Guides <i class="fas fa-arrow-right text-[8px]"></i></a>
                        </div>
                    </div>

                    <!-- News Widget -->
                    <div class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[32px] glass">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-[11px] uppercase tracking-widest opacity-60 text-slate-900 dark:text-white">Chess News</h4>
                            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                        </div>
                        <h3 class="font-bold text-sm mb-3 text-slate-900 dark:text-white">Ascending Pawn Welcomes 1000th Member!</h3>
                        <p class="text-xs text-slate-500 mb-4">Our community continues to grow at an unprecedented pace. Thank you for making us Kenya's #1 chess destination.</p>
                        <hr class="border-slate-100 dark:border-slate-800 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex -space-x-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 border-2 border-white dark:border-slate-900"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-300 border-2 border-white dark:border-slate-900"></div>
                                <div class="w-6 h-6 rounded-full bg-slate-400 border-2 border-white dark:border-slate-900"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">12m ago</span>
                        </div>
                    </div>
                </div>

                <!-- Community Outreach & Badges / Discord -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brandGreen/10 flex items-center justify-center text-brandGreen">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold uppercase tracking-tight text-slate-900 dark:text-white">Community Outreach</h2>
                    </div>

                    <div class="aspect-video rounded-[40px] overflow-hidden relative group shadow-lg">
                        <img src="https://images.unsplash.com/photo-1529699211952-734e80c4d42b?auto=format&fit=crop&q=80&w=1200" class="w-full h-full object-cover grayscale transition-all duration-700 group-hover:grayscale-0 scale-105 group-hover:scale-100" alt="Outreach">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/70 to-black/20 p-8 md:p-10 flex flex-col justify-end">
                            <span class="text-brandGreen font-black text-xs uppercase tracking-widest mb-2 drop-shadow-md">Community Outreach</span>
                            <h3 class="text-white text-2xl font-bold uppercase tracking-tight mb-4 drop-shadow-lg">Chess in the Park: Nyeri Edition</h3>
                            <p class="text-white/90 font-medium text-sm md:text-base max-w-xl mb-6 drop-shadow-md">Every first Sunday of the month, we bring the game to the heart of the city, teaching kids and hosting casual blitz matches for everyone.</p>
                            <a href="donate.php" class="self-start px-6 py-3 bg-white text-black text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-brandGreen/80 hover:scale-105 hover:text-white transition-all duration-300 text-center shadow-lg hover:shadow-brandGreen/30">Support Initiative</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievements & Discord Row -->
            <div class="mt-24 pt-24 border-t border-slate-200 dark:border-slate-800">
                <div class="grid lg:grid-cols-3 gap-12 animate-slide-up">
                    <div class="lg:col-span-2">
                        <h4 class="font-bold text-[11px] uppercase tracking-[0.2em] text-slate-400 mb-6">Member Achievements</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1 shadow-sm">
                                <div class="w-12 h-12 bg-brandOrange/10 text-brandOrange rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Tournament Ace</p>
                            </div>
                            <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1 shadow-sm">
                                <div class="w-12 h-12 bg-brandGreen/10 text-brandGreen rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Blitz Master</p>
                            </div>
                            <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1 shadow-sm">
                                <div class="w-12 h-12 bg-red-400/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white">10 Win Streak</p>
                            </div>
                            <div class="p-6 bg-white dark:bg-slate-900/40 rounded-3xl text-center border border-slate-100 dark:border-slate-800 transition-transform hover:-translate-y-1 shadow-sm">
                                <div class="w-12 h-12 bg-brandGold/10 text-brandGold rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Grand Guardian</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 bg-slate-900 dark:bg-slate-100 rounded-[40px] text-white dark:text-black flex flex-col justify-between shadow-xl">
                        <div>
                            <h3 class="font-black text-xl leading-tight mb-4 uppercase">Discuss <br>Strategy</h3>
                            <p class="text-xs opacity-70 mb-6">Join our private Discord for real-time discussions, game analysis, and member socialization.</p>
                        </div>
                        <a href="#" class="inline-flex items-center gap-3 font-bold text-xs uppercase tracking-widest border-b-2 border-brandGreen pb-1 hover:gap-5 transition-all self-start">Join Discord <i class="fab fa-discord"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admin image uploader (only for admins) -->
    <?php if (function_exists('is_admin_user') && is_admin_user()): ?>
    <script>
        (function() {
            const style = document.createElement('style');
            style.textContent = [
                '.admin-image-wrapper .admin-upload-btn {',
                'position:absolute;',
                'top:12px;',
                'right:12px;',
                'z-index:60;',
                'background:rgba(0,0,0,0.75);',
                'color:#fff;',
                'border:1px solid rgba(255,255,255,0.35);',
                'border-radius:9999px;',
                'padding:8px 12px;',
                'font-size:11px;',
                'font-weight:700;',
                'letter-spacing:0.04em;',
                'text-transform:uppercase;',
                'cursor:pointer;',
                'opacity:0;',
                'transform:translateY(-6px);',
                'transition:all 0.18s ease;',
                'pointer-events:none;',
                'backdrop-filter:blur(6px);',
                '-webkit-backdrop-filter:blur(6px);',
                '}',
                '.admin-image-wrapper:hover .admin-upload-btn {',
                'opacity:1;',
                'transform:translateY(0);',
                'pointer-events:auto;',
                '}',
                '.admin-image-wrapper {',
                'outline:2px dashed rgba(128,210,0,0);',
                'outline-offset:-2px;',
                'transition:outline-color 0.18s ease;',
                '}',
                '.admin-image-wrapper:hover {',
                'outline-color:rgba(128,210,0,0.75);',
                '}'
            ].join('');
            document.head.appendChild(style);

            const uploaderInput = document.createElement('input');
            uploaderInput.type = 'file';
            uploaderInput.accept = 'image/*';
            uploaderInput.style.display = 'none';
            document.body.appendChild(uploaderInput);

            let activeImg = null;
            let activeButton = null;

            document.querySelectorAll('img.admin-editable[data-image-key]').forEach(function(img) {
                const imageKey = img.getAttribute('data-image-key');
                let wrapper = img.parentElement;
                if (imageKey === 'hero_background') {
                    const heroSection = img.closest('section');
                    if (heroSection) wrapper = heroSection;
                }
                if (!wrapper) return;

                const wrapperStyle = window.getComputedStyle(wrapper);
                if (wrapperStyle.position === 'static') {
                    wrapper.style.position = 'relative';
                }

                wrapper.classList.add('admin-image-wrapper');

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'admin-upload-btn';
                button.textContent = 'Update photo';
                button.title = 'Upload a new image';

                button.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    activeImg = img;
                    activeButton = button;
                    uploaderInput.value = null;
                    uploaderInput.click();
                });

                wrapper.appendChild(button);
            });

            uploaderInput.addEventListener('change', async function() {
                if (!activeImg) return;
                const file = uploaderInput.files[0];
                if (!file) return;

                const key = activeImg.getAttribute('data-image-key');
                const fd = new FormData();
                fd.append('image', file);
                fd.append('key', key);

                try {
                    if (activeButton) {
                        activeButton.disabled = true;
                        activeButton.textContent = 'Uploading...';
                    }

                    const res = await fetch('upload_home_image.php', { method: 'POST', body: fd });
                    const json = await res.json();
                    if (json.success) {
                        // update image src (cache-bust)
                        activeImg.src = json.path + '?t=' + Date.now();
                        // small flash
                        activeImg.style.transition = 'filter 0.3s ease';
                        activeImg.style.filter = 'grayscale(100%)';
                        setTimeout(function() { activeImg.style.filter = ''; }, 300);
                        if (activeButton) {
                            activeButton.textContent = 'Updated';
                            setTimeout(function() {
                                if (activeButton) activeButton.textContent = 'Update photo';
                            }, 800);
                        }
                        activeImg = null;
                    } else {
                        alert('Upload failed: ' + (json.message || 'unknown'));
                    }
                } catch (err) {
                    alert('Upload error');
                } finally {
                    if (activeButton) {
                        activeButton.disabled = false;
                    }
                    activeButton = null;
                }
            });
        })();
    </script>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
