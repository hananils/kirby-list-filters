export default function license(name) {
    if (window.panel.user.id === null) {
        return;
    }

    window.panel.api
        .get(name + '/license', { locale: window.panel.translation.code })
        .then((response) => {
            if (response.valid === false) {
                console.error(response.message);

                if (response.debug === false) {
                    window.panel.notification.open({
                        message: response.message,
                        theme: 'error',
                        icon: 'key',
                        timeout: false
                    });
                }
            }
        });
}
