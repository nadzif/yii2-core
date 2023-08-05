$(window).on('load', function () {

    // demo loader
    // $("#loading-center").click(function() {
    //     $("#loading").fadeOut(500);
    // })

    $("#loading").fadeOut(500);
});

$(function () {
    const FloatAlert = {
        tempalte: '.alert-template',
        wrapper: '.alert-box',
        time: 5000,

        init: function () {
            self = this;
            window.FloatAlert = FloatAlert;

            $(this.wrapper).find(".alert").each(function () {
                self.alertEvent(this);
            });

            $(this.wrapper).on('DOMNodeInserted', ".alert", function () {
                $(this).find('.loading').css('width');
                self.alertEvent(this);
            });
        },

        remove: function (el) {
            $(el).find('.loading').css({'width': '100%', 'transition': 'width ' + this.time + 'ms'});
            return setTimeout(() => {
                $(el).remove()
            }, this.time);
        },

        alertEvent: function (el) {
            var self = this;
            var timeout = self.remove(el);

            $(el).on('mouseover', function () {
                $(this).find('.loading').css({'width': '0', 'transition': 'width 500ms'});
                clearTimeout(timeout);
            });

            $(el).on('mouseleave', function () {
                timeout = self.remove(this);
            });
        },

        getTemplate: function (type) {
            var classType = 'alert-success';
            switch (type) {
                case "danger":
                    classType = 'alert-danger';
                    break;
                case "warning":
                    classType = 'alert-warning';
                    break;
                case "info":
                    classType = 'alert-info';
                    break;
                default:
                    classType = 'alert-success';
            }

            var tc = $(this.tempalte)[0].outerHTML;
            var template = $($.parseXML(tc)).contents();
            template = $(template).find('.alert').addClass(classType).wrap('<div class="wrap-unwrap"></div>');

            return template.parent().html();
        },

        alert: function (tittle = '', message = '', type = 'success', icon) {
            if (typeof icon !== 'undefined' && !(/<[a-z][\s\S]*>/i.test(icon))) {
                icon = '<i class="' + icon + '"></i>';
            }

            var template = this.getTemplate(type);
            var content = template.replace('_TITLE_', tittle).replace('_MESSAGE_', message);


            if (icon) {
                content = content.replace('_ICON_', icon);
            } else {
                content = content.replace('alert-icon', 'd-none');
            }

            $(this.wrapper).append(content);
        },

        successAlert: function (title, message, icon) {
            this.alert(title, message, 'success', icon);
        },

        dangerAlert: function (title, message, icon) {
            this.alert(title, message, 'danger', icon);
        },

        warningAlert: function (title, message, icon) {
            this.alert(title, message, 'warning', icon);
        },

        infoAlert: function (title, message, icon) {
            this.alert(title, message, 'info', icon);
        },

        renderAlert: function (data, icons = {}) {

            console.log('data', data);
            console.log('icons', icons);

            icons = Object.assign({}, this.defaultIcons(), icons);

            try {
                data = JSON.parse(data);
            } catch (err) {
                console.log(err.message);
            }


            if (data.data !== undefined && data.data.alert !== undefined) {
                let alertObject = data.data.alert;
                if (Array.isArray(alertObject)) {

                    for (let i in alertObject) {
                        const alertData = alertObject[i];
                        let alertIcon = icons.success;

                        switch (alertData.type) {
                            case 'warning':
                                alertIcon = icons.warning;
                                break;
                            case 'danger':
                                alertIcon = icons.danger;
                                break;
                            case 'info':
                                alertIcon = icons.info;
                                break;
                        }

                        this.alert(alertData.title, alertData.message, alertData.type, alertIcon);
                    }
                } else {
                    this.alert(alertObject.title, alertObject.message, alertObject.type, icons.success);
                }
            }
        },

        defaultIcons: function (type = null) {
            const icons = {
                'warning': '<i class="ion-android-warning"></i>',
                'info': '<i class="ion-information"></i>',
                'success': '<i class="ion-ios-checkmark-outline"></i>',
                'danger': '<i class="ion-ios-flame"></i>'
            };

            return type !== null ? icons[type] : icons;
        }
    };
    FloatAlert.init();
});
