jQuery(document).ready(function($) {
    $('.tab-button button').on('click', function() {
        var index = $(this).index();
        $('.tab-button button').removeClass('active');
        $('.tab-pannel').removeClass('active');
        $(this).addClass('active');
        $('.tab-pannel').eq(index).addClass('active');

    });

});



jQuery(document).ready(function($) {

    jQuery('.dropdown-button').on('click', function(e) {
        e.stopPropagation();

        jQuery('.dropdown-nav').stop(true, true).slideToggle(200);
    });

    jQuery(document).on('click', function() {
        jQuery('.dropdown-nav').stop(true, true).slideUp(200);
    });

    jQuery('.dropdown-nav').on('click', function(e) {
        e.stopPropagation();
    });

});


jQuery(document).ready(function ($) {
    $('.job-content').hide();
    $('#job-1').fadeIn(250);
    $('.candidate-job-card').on('click', function () {
        var jobID = $(this).data('job');
        $('.candidate-job-card').removeClass('active');
        $(this).addClass('active');
        $('.job-content').stop(true, true).fadeOut(150);
        $('#' + jobID).stop(true, true).fadeIn(300);

    });

});