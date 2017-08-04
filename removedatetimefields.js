jQuery(document).ready(function() {
    jQuery("#EventInfo tr:nth-child(2)").hide();
    jQuery("#EventInfo tr:nth-child(2)").after( '<div class="ondemandNotice"><p>Time and Date disabled for On-Demand Webinars.</p></div>' );
});
