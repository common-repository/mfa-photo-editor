let sos_frame_ml_overlay = null;
let sos_frame_ml_scenario = null;
let sos_counter = 0;

function jsSosGalCreateFrameML(title)
{
    let ret = wp.media({
        title: title,
        library: { type: 'image' },
        button: {
            text: sosgal_local.js_ml_button
        },
        multiple: 'add'	// 'true' => allows multiple files to be selected via click+CTRL; 'add' => the same but without CTRL key
    });

    return ret;
}

function jsSosGalSelOverlayFromML(e)
{
    e.preventDefault();
    if (!sos_frame_ml_overlay)
    {
        let title = sosgal_local.js_ml_title_overlay;
        sos_frame_ml_overlay = jsSosGalCreateFrameML(title);
        let cat = 'sos-gal-list-overlay';
        sos_frame_ml_overlay.on( 'select', function() {
            let images = sos_frame_ml_overlay.state().get('selection').toJSON(); //array
            for (let n=0; n<images.length; n++)
            {
                let img = images[n];
                if (img.id && img.sizes.thumbnail.url)
                {
                    jsSosAddGalItem(cat, img.id, img.sizes.thumbnail.url);
                }
                else
                {
                    alert(sosgal_local.js_problem);
                }
            }
        });
    }
    sos_frame_ml_overlay.open();
}
function jsSosGalSelScenarioFromML(e)
{
    e.preventDefault();
    if (!sos_frame_ml_scenario)
    {
        let title = sosgal_local.js_ml_title_scenario;
        sos_frame_ml_scenario = jsSosGalCreateFrameML(title);
        let cat = 'sos-gal-list-scenario';
        sos_frame_ml_scenario.on( 'select', function() {
            let images = sos_frame_ml_scenario.state().get('selection').toJSON(); //array
            for (let n=0; n<images.length; n++)
            {
                let img = images[n];
                if (img.id && img.sizes.thumbnail.url)
                {
                    jsSosAddGalItem(cat, img.id, img.sizes.thumbnail.url);
                }
                else
                {
                    alert(sosgal_local.js_problem);
                }
            }
        });
    }
    sos_frame_ml_scenario.open();
}

function jsSosDelGalItem(id)
{
    id = '#' + id;
    let item = jQuery(id);
    let status = jQuery('#status' , item).get(0);
    let img = jQuery('img' , item).get(0);
    let ico = jQuery('i' , item).get(0);

    if (status.value == 1)
    {
        img.style.opacity = '0.5';
        img.style.filter = 'grayscale(100%)';
        ico.innerHTML = 'undo';
        ico.style.color = 'blue';
        ico.title = sosgal_local.js_ico_trash_undo;
        status.value = -1;
    }
    else
    {
        img.style.opacity = '1.0';
        img.style.filter = 'none';
        ico.innerHTML = "delete_forever";
        ico.style.color = 'red';
        ico.title = sosgal_local.js_ico_trash_delete;
        status.value = 1;
    }
}

function jsSosAddGalItem(list, id, url) //, n
{
    sos_counter++;
    let unique_id = list + '_' + id + '_' + sos_counter;

    let li = document.createElement("li");
    li.setAttribute("class", "sos-gal-item");
    li.setAttribute("id", unique_id);

    let ico = document.createElement("i");
    ico.setAttribute("class", "material-icons");
    ico.innerHTML = "delete_forever";
    ico.setAttribute("onclick","jsSosDelGalItem('" + unique_id + "');");
    ico.setAttribute("title", sosgal_local.js_ico_trash_delete);

    let img = document.createElement("img");
    img.setAttribute("alt", "image");
    img.setAttribute("title", sosgal_local.js_img_item_title);
    img.setAttribute("src", url);
    //img.setAttribute("class", "tn");

    let reference = document.createElement("input");
    reference.setAttribute("type", "hidden");
    reference.setAttribute("id", "id");
    reference.setAttribute("value", id);

    let status = document.createElement("input");
    status.setAttribute("type", "hidden");
    status.setAttribute("id", "status");
    status.setAttribute("value", 1);

    li.appendChild(img);
    li.appendChild(ico);
    li.appendChild(reference);
    li.appendChild(status);

    let ul = document.getElementById(list);
    ul.appendChild(li);
}
function jsSosGalRemoveDeleted()
{
    jQuery('#sos-gal-box-overlay ul li').each(function(i)
    {
        if (jQuery('#status', this).attr('value') == -1)
        {
            jQuery(this).remove();
        }
    });
    jQuery('#sos-gal-box-scenario ul li').each(function(i)
    {
        if (jQuery('#status', this).attr('value') == -1)
        {
            jQuery(this).remove();
        }
    });
}

function jsSosGalGenerate()
{
    //overlay
    let overlays = new Array();
    jQuery('#sos-gal-box-overlay ul li').each(function(i)
    {

        let status = jQuery('#status', this).attr('value');
        if (status == 1)
        {
            let id = jQuery('#id', this).attr('value');
            overlays.push( id );
        }
    });
    let scenarios = new Array();
    jQuery('#sos-gal-box-scenario ul li').each(function(i)
    {

        let status = jQuery('#status', this).attr('value');
        if (status == 1)
        {
            let id = jQuery('#id', this).attr('value');
            scenarios.push( id );
        }
    });

    let data =  { 'overlay' : overlays ,'scenario' : scenarios };
    jsSosGalSave(data, function(res) {
        if ( res.status == 1 )
        {
            jsSosGalRemoveDeleted();
        }
        jsSosAlert(res);
    });


}

function jsSosGalSave(data, callback)
{
    let ret = { status:0, error: true, message: 'Unhandled Rest API problem.', title: null, icon: 'error' };
    if ( typeof data === 'object' && typeof callback === 'function' )
    {
        let loader = document.getElementById('sos-gal-loader');
        if (loader) { loader.style.display = 'inline';}
        jQuery.ajax({
             url: sosgal_api.url
            ,type: sosgal_api.method
            ,data: JSON.stringify(data)
            ,headers: { 'X-WP-Nonce': sosgal_api.nonce }
        }).done(function( response, textStatus, xhr ) {
            try {
                ret = jsSosGalLoadReturn(ret, response);
            }
            catch (ex) {
                ret.title = 'browser exception';
                ret.message = '(' + ex.name + ') ' + ex.message;
            }
        }).fail(function( xhr, status, errorThrown ) {
            ret.message = xhr.status + ': ' + errorThrown;
        }).always( function( resp, status ) {
            if (loader) { loader.style.display = 'none';}
            callback( ret );
        });
    }
    else
    {
        let msg = 'Invalid javascript parameter(s):';
        if (typeof data !== 'object')
        {
            msg += '\ndata';
        }
        if (typeof callback !== 'function')
        {
            msg += '\ncallback';
        }
        jsSosAlert( { message:msg, icon:'error' } );
    }
}

if(window.jQuery){
    (function($){
        'use strict';
        $(document).ready(function()
        {
            if ($.ajax)
            {
                $( "#sos-gal-list-overlay" ).sortable();
                $( "#sos-gal-list-overlay" ).disableSelection();
                $( "#sos-gal-list-scenario" ).sortable();
                $( "#sos-gal-list-scenario" ).disableSelection();

                $('#sos-gal-btn-add-overlay').on('click', function(e){ jsSosGalSelOverlayFromML(e);} ).removeAttr('disabled');
                $('#sos-gal-btn-add-scenario').on('click', function(e){ jsSosGalSelScenarioFromML(e);} ).removeAttr('disabled');


                //$('#sos-gal-btn-save').on('click', function(){ jsSosGalRemoveDeleted(); } ).removeAttr('disabled');;
                $('#sos-gal-btn-save').on('click', function(e){ jsSosGalGenerate(e); } ).removeAttr('disabled');;

                jsSosGalInitialize();
            }
            else
            {
                jsSosAlert( { message:'Ajax not found.', icon:'error'} );
            }
        });
    })(jQuery);
}
else
{
    jsSosAlert( { message:'jQuery not found.', icon:'error'} );
}

function jsSosGalLoadReturn(ret, res)
{
    let props = Object.getOwnPropertyNames(ret);
    for (let n=0; n<props.length; n++)
    {
        let p = props[n];
        if ( res.hasOwnProperty(p) )
        {
            ret[p] = res[p];
        }
    }
    return ret;
}
function jsSosAlert(alert)
{
    if (typeof Swal !== 'undefined')
    {
        if (!alert.message.includes('\n'))
        {
            Swal.fire({
                text: alert.message
                ,icon: alert.icon
                ,title: alert.title
            });
        }
        else
        {
            Swal.fire({
                html: alert.message.replace(/\n/g, '<br>')
                ,icon: alert.icon
                ,title: alert.title
            });
        }
    }
    else
    {
        alert(alert.message);
    }
}
function jsSosCopy2CB(id, message = 'Text copied into clipboard:')
{
    let msg = message + '\n\n';
    let txt = document.getElementById(id);
    if (txt)
    {
        txt.select();
        txt.setSelectionRange(0, 99999); /* For mobile devices */
        if ( document.execCommand("copy") )
        {
            msg += txt.value;
        }
        else
        {
            msg = "Your browser didn't copy the content";
        }
        txt.blur();
    }
    else
    {
        msg = "Your browser couldn't copy the content";
    }
    alert(msg);
}

function jsSosShowHideBlock(id)
{
    let obj = document.getElementById(id);
    if (obj && obj.style && obj.style.display) {
        if (obj.style.display == 'none') {
            obj.style.display = 'block';
        } else {
            obj.style.display = 'none';
        }
    }
}