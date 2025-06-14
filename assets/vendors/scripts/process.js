var width = 100,
    perfData = window.performance.timing,
    EstimatedTime = 2000; // Fixed to 2000 milliseconds (2 seconds)
    
// Percentage Increment Animation
var PercentageID = $("#percent1"),
    start = 0,
    end = 100,
    duration = EstimatedTime; // Use the fixed time

animateValue(PercentageID, start, end, duration);

function animateValue(id, start, end, duration) {
    var range = end - start,
        current = start,
        increment = end > start ? 1 : -1,
        stepTime = Math.abs(Math.floor(duration / range)),
        obj = $(id);
    
    var timer = setInterval(function() {
        current += increment;
        $(obj).text(current + "%");
        $("#bar1").css('width', current + "%");
        
        if (current === end) {
            clearInterval(timer);
        }
    }, stepTime);
}

// Fading Out Loadbar on Finished
setTimeout(function() {
    $('.pre-loader').fadeOut(300);
}, duration); // Use the fixed duration
