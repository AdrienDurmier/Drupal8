jQuery(document).ready(function () {
    // Optimisation de la performance en ne chargeant que la vidéo sélectionnée
    jQuery('.video_lien').click(function() {
        var video_id = jQuery(this).attr("video-id");
        var video_playlist = jQuery(this).attr("video-playlist");
        var iframe_html = '<iframe src="https://www.youtube-nocookie.com/embed/'+video_id+'?list='+video_playlist+'" frameborder="0" allowfullscreen></iframe>';
        jQuery('#modalVideo').find('.modal-body').html(iframe_html);
    });
    // Script permettant de régler automatiquement la hauteur d'une vidéo en fonction de sa largeur ratio de 355/196
    var largeur_apercu_video = jQuery('.video_thumb').width();
    jQuery('.video_thumb').css('height',largeur_apercu_video*(196/355));
    jQuery('.video_thumb').css('line-height',largeur_apercu_video*(196/355) + 'px');
});

jQuery(window).resize(function() {
    // Script permettant de régler automatiquement la hauteur d'une vidéo en fonction de sa largeur ratio de 355/196
    var largeur_apercu_video = jQuery('.video_thumb').width();
    jQuery('.video_thumb').css('height',largeur_apercu_video*(196/355));
    jQuery('.video_thumb').css('line-height',largeur_apercu_video*(196/355) + 'px');
});