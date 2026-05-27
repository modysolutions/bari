// get curl response from https://tobaccointelligence.com.local/wp-json/tamarind/v1/support-country/?code=nl
function get_access_country(code) {
    return fetch('/wp-json/tamarind/v1/support-country/?code=' + code)
        .then(response => response.json())
        .then(data => {
            // console.log(data);
            return data.access;
        })
        .catch(error => {
            console.error(error);
        });
}

function get_video () {
    return fetch('/wp-json/tamarind/v1/get-china-video/?post_id=' + china_vars.post_id)
        .then(response => response.json())
        .then(data => {
            return data.china_video;
        })
        .catch(error => {
            console.error(error);
        });
}

function get_form () {
    return fetch('/wp-json/tamarind/v1/get-china-form/?post_id=' + china_vars.post_id)
        .then(response => response.json())
        .then(data => {
            return data;
        })
        .catch(error => {
            console.error(error);
        });
}

function set_cookie ( name_cookie ) {
    let date = new Date();
    date.setTime(date.getTime() + (1 * 24 * 60 * 60 * 1000));
    let expires = "expires="+ date.toUTCString();
    document.cookie = name_cookie +"=1;" + expires + ";path=/";
}

function get_cookie ( name_cookie ) {
    let name = name_cookie + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    let result = false;
    ca.forEach(function(cookie) {
        while (cookie.charAt(0) == ' ') {
            cookie = cookie.substring(1);
        }
        if (cookie.indexOf(name) == 0) {
            result = true;
        }
    });
    return result;
}

/**
 * Show popup modal with cookie tracking and auto-removal
 * @param {string} class_id - The modal selector (e.g., '#my-modal')
 */
function show_popup(class_id) {
    const name_cookie = class_id.replace('#', '');
    
    if (get_cookie(name_cookie) === true) {
        return;
    }

    set_cookie(name_cookie);

    const modal = document.querySelector(class_id);
    
    if (!modal) {
        console.warn('Modal not found:', class_id);
        return;
    }

    if (!modal.classList.contains('tm-modal')) {
        modal.classList.add('tm-modal');
    }

    const handleClose = () => {
        console.log('Removing modal:', class_id);
        modal.remove();
    };

    modal.addEventListener('tm-modal-close', handleClose, { once: true });

    // Open modal
    if (window.tmModal) {
        window.tmModal.openModalById(class_id);
    } else {
        console.warn('TMModal not available, opening modal manually');
        modal.showModal();
    }
}

function get_widget () {
    return fetch('/wp-json/tamarind/v1/get-china-widget/')
        .then(response => response.json())
        .then(data => {
            return data.china_widget;
        })
        .catch(error => {
            console.error(error);
        });
}

function widget_listeners() {

    setTimeout(function() {
        let geoWidgetChina = document.getElementById('geo-widget-china');

        // addEventListener to open widget when hover
        geoWidgetChina.addEventListener('mouseover', function(e) {
            geoWidgetChina.style.right = '0';
        });

        // addEventListener to close widget when hover
        geoWidgetChina.addEventListener('mouseout', function(e) {
            geoWidgetChina.style.right = '-210px';
        });

        let geoWidgetChinaQR = document.getElementById('geo-widget-china-qr');

        // addEventListener to open widget when hover
        geoWidgetChinaQR.addEventListener('mouseover', function(e) {
            geoWidgetChinaQR.querySelector('.china-widget-qr').style.display = 'block';
        });

        // addEventListener to close widget when hover
        geoWidgetChinaQR.addEventListener('mouseout', function(e) {
            geoWidgetChinaQR.querySelector('.china-widget-qr').style.display = 'none';
        });

    }, 1000);

}

var is_china = false;
// Caso de prueba con Nederlands
// let show_chino = get_access_country('nl');
// ZH código para Chino
// language de navegador para chino puede ser: zh, zh-HK, zh-CN, zh-TW, zh-SG, zh-MO
// Código IP para chino puede ser: CN, HK, TW, SG, MO
let show_chino = get_access_country('zh');

show_chino.then(function(result) {
    if (result === true) {
        // console.log('Chinesse...!!');
        is_china = true;
    } else {
        // console.log('Nothing...');
    }

    if (is_china) {
        let dummy = document.querySelector('.china-dummy');
        
        let show_widget = get_widget();
        show_widget.then(function(result) {
            if (result !== false) {
                dummy.innerHTML += result;
                widget_listeners();        
            }
        });

        let show_form = get_form();
        show_form.then(function(result) {
            if (result.china_form !== false) {
                dummy.innerHTML += result.china_form;

                setTimeout(function() {
                    show_popup( "#geo-form-china-modal" );
                }, 500);
            }
        });

        let show_video = get_video();
        show_video.then(function(result) {
            if (result !== false) {
                dummy.innerHTML += result;

                setTimeout(function() {
                    show_popup( "#geo-video-china-modal" );
                }, 500);
            }
        });
    }
});
