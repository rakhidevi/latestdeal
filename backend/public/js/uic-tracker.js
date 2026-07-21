/**
 * User Intelligence Center (UIC) - Frontend Tracker
 * Tracks visitors, sessions, pageviews, and events.
 */

const UICTracker = (function() {
    // Configuration
    const ENDPOINT = '/api/uic/track';
    const FLUSH_INTERVAL = 5000; // 5 seconds
    
    // State
    let visitorUuid = localStorage.getItem('uic_v_uuid');
    let sessionId = sessionStorage.getItem('uic_s_id');
    let eventQueue = [];
    let pageEnterTime = Date.now();
    let maxScroll = 0;
    
    // Utilities
    function generateUuid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function initIds() {
        if (!visitorUuid) {
            visitorUuid = generateUuid();
            localStorage.setItem('uic_v_uuid', visitorUuid);
        }
        if (!sessionId) {
            sessionId = generateUuid();
            sessionStorage.setItem('uic_s_id', sessionId);
        }
        
        // Sync to cookies for server-side reading (e.g. RedirectController)
        document.cookie = `uic_vid=${visitorUuid}; path=/; max-age=31536000`;
        document.cookie = `uic_sid=${sessionId}; path=/; max-age=86400`;
    }
    
    function getUtmParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            utm_source: urlParams.get('utm_source'),
            utm_medium: urlParams.get('utm_medium'),
            utm_campaign: urlParams.get('utm_campaign')
        };
    }

    // Build standard payload envelope
    function buildPayload() {
        const utms = getUtmParams();
        return {
            visitor_uuid: visitorUuid,
            session_id: sessionId,
            url: window.location.href,
            referrer: document.referrer,
            device: /Mobi|Android/i.test(navigator.userAgent) ? 'Mobile' : 'Desktop',
            browser: navigator.userAgent, // simplified, backend can parse if needed
            os: navigator.platform,
            screen_resolution: `${window.screen.width}x${window.screen.height}`,
            language: navigator.language,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            utm_source: utms.utm_source,
            utm_medium: utms.utm_medium,
            utm_campaign: utms.utm_campaign,
            events: [...eventQueue] // clone array
        };
    }

    function flush() {
        if (eventQueue.length === 0) return;
        
        const payload = buildPayload();
        const data = JSON.stringify(payload);
        
        // Use sendBeacon if available, otherwise fetch
        if (navigator.sendBeacon) {
            navigator.sendBeacon(ENDPOINT, data);
        } else {
            fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: data,
                keepalive: true
            });
        }
        
        // Clear queue
        eventQueue = [];
    }

    // Auto-trackers
    function trackPageView() {
        eventQueue.push({
            type: 'PAGE_VIEW',
            url: window.location.href,
            title: document.title,
            duration_seconds: Math.round((Date.now() - pageEnterTime) / 1000),
            scroll_depth: maxScroll
        });
        flush();
    }
    
    function trackScroll() {
        let scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
        if (scrollPercent > maxScroll && scrollPercent <= 100) {
            maxScroll = scrollPercent;
        }
    }

    function setupListeners() {
        // Track scroll depth
        window.addEventListener('scroll', () => {
            requestAnimationFrame(trackScroll);
        }, { passive: true });

        // Flush on visibility change / exit
        window.addEventListener('visibilitychange', function logData() {
            if (document.visibilityState === 'hidden') {
                // Update duration before flushing
                if(eventQueue.length === 0) {
                   eventQueue.push({
                       type: 'PAGE_EXIT',
                       duration_seconds: Math.round((Date.now() - pageEnterTime) / 1000),
                       scroll_depth: maxScroll
                   });
                }
                flush();
            }
        });

        window.addEventListener('pagehide', flush);

        // Periodically flush events
        setInterval(flush, FLUSH_INTERVAL);
    }

    // Public API
    return {
        init: function() {
            initIds();
            setupListeners();
            trackPageView(); // Track initial load
        },
        
        trackEvent: function(type, name, metadata = {}) {
            eventQueue.push({
                type: type,
                name: name,
                metadata: metadata
            });
            // If it's a critical event, maybe flush immediately
            if(type === 'AFFILIATE_CLICK' || type === 'AI_QUESTION') {
                flush();
            }
        },
        
        getIds: function() {
            return { visitorUuid, sessionId };
        }
    };
})();

// Initialize automatically on load
document.addEventListener('DOMContentLoaded', () => {
    UICTracker.init();
});
