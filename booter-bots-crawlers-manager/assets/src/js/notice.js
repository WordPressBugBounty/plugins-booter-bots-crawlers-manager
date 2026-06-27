let notice = document.querySelector('.js-booter-404-notice');
if(notice) {
    notice.addEventListener('click', function(e) {

        if(e.target.classList.contains('notice-dismiss')) {
            document.cookie = `booter_404_notice_dismissed=1;path=/`;
            return;
        }

        if(e.target.dataset.slugs) {

            if(!confirm(window.wp_booter_notices.confirm_disable_plugins)) {
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.wp_booter_notices.ajax_url, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.addEventListener('load', () => {
                let url = window.location.href;
                window.location.href = url + (url.indexOf('?') >= 0 ? "&" : '?') + 'booter-disabled-plugins=yes';
            });

            let slugs = '';
            e.target.dataset.slugs.split(',').forEach((s, i) => slugs += `&slugs[${i}]=${encodeURIComponent(s)}`);

            xhr.send(`action=booter_disable_404_plugins&_ajax_nonce=${encodeURIComponent(window.wp_booter_notices.ajax_nonce)}${slugs}`);

            e.target.innerHTML = '<span class="spinner is-active" style="width: 1em; height: 1em; background-size: 1em;"></span>';
        }
    });
}
