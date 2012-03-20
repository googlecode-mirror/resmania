Shadowbox.init({
    language:   RM.Common.Locale,
    players:    ["iframe","img"],
    modal: true
});

/**
 * this invokes the shadowbox
 *
 * @params  url             string  the url of the content to load
 * @params  targetUrl       string  the target url we want to go to when it's closed (noreload just closes)
 * @params  width           int     the width of the iframe
 * @params  height          int     the height of the iframe
 * @params  playerType      string  iframe or img
 */
function RM_doShadowBox(url, targetUrl, width, height, playerType){

    if (!url) return;
    //if (!width) width = 300;
    //if (!height) height = 300;
    if (!playerType) playerType = "iframe";

    Shadowbox.open({
        content:    url,
        method:     'GET',
        player:     playerType,
        height:     height,
        width:      width,
        options:{
            onClose: function(){
                if (targetUrl=="noreload"){return;}
                if (targetUrl=="self"){
                    window.location.reload();
                } else if (targetUrl != ""){
                    window.location = targetUrl;
                }
            }
        }
    });
}