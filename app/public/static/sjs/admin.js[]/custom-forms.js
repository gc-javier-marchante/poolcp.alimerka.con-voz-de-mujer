"use strict";

let gcAjaxCallbacks = {};
window.gcAjaxCallbacks = gcAjaxCallbacks;

gcAjaxCallbacks.reload = function () {
    location.reload();
};

gcAjaxCallbacks.userEmailChanged = function (target) {
    $('[data-user-email]').text($(target).find('[name="user[User][email]"]').val());
};

gcAjaxCallbacks.userAvatarChanged = function (target, result) {
    $('[data-user-avatar]').each(function () {
        if ($(this).is('img')) {
            this.src = result.response.avatar_url;
        }
    });
    $('[data-user-first-name]').text($(target).find('[name="user[User][first_name]"]').val());
};

gcAjaxCallbacks.userTwoFactorEnabled = function () {
    $('[data-two-factor-container]').addClass('d-status');
};

gcAjaxCallbacks.userTwoFactorDisabled = function () {
    $('[data-two-factor-container]').removeClass('d-status');
};

gcAjaxCallbacks.remove = function (target) {
    let $remove = $(target);
    $remove.parents('form').attr('data-gc-has-changes', 'true');

    if (!$remove.is('[data-remove]')) {
        $remove = $remove.parents('[data-remove]');
    }

    $remove.fadeOut(function () {
        if ($remove.is('tr')) {
            let $datatable = $remove.parents('[data-datatable]').eq(0);

            if ($datatable.length == 1) {
                let datatable = KTUtil.data($datatable.get(0)).get('datatables').datatable;
                datatable.row($remove.get(0)).remove().draw(false);
                return;
            }
        }

        $remove.remove();
    });
};

gcAjaxCallbacks.moveUp = function (target) {
    let $remove = $(target);

    if (!$remove.is('[data-remove]')) {
        $remove = $remove.parents('[data-remove]');
    }

    //$remove.parents('form').attr('data-gc-has-changes', 'true'); // Move is autosaved, no need for this

    const row_index = $remove.index();
    const $datatable = $remove.parents('[data-datatable]').eq(0);

    if ($datatable.length == 1) {
        const datatable = KTUtil.data($datatable.get(0)).get('datatables').datatable;
        const datatable_row_index = datatable.row($remove.get(0)).index();

        if (datatable_row_index == 0) {
            return;
        }

        const data1 = datatable.row(datatable_row_index).data();
        const data2 = datatable.row(datatable_row_index - 1).data();
        data1.order += -1;
        data2.order += 1;
        datatable.row(datatable_row_index).data(data2);
        datatable.row(datatable_row_index - 1).data(data1);

        if (row_index == 0) {
            datatable.page(datatable.page() - 1);
        }

        datatable.row($remove.get(0)).draw(false);
        KTUtil.domAdded();
    } else {
        if (row_index == 0) {
            if (window.URLSearchParams) {
                const urlParams = new URLSearchParams(window.location.search);
                const p = urlParams.get('p');

                if (p && p > 1) {
                    urlParams.set(p, p - 1);
                    location.href = '?' + urlParams.toString();
                    return;
                }
            }

            location.reload();
        } else {
            $remove.parent().children().eq($remove.index() - 1).before($remove);
        }
    }
};

gcAjaxCallbacks.addToList = function (target, result) {
    GCSelectToList.addIdToList(target, result.response.addToList.id, $('[data-select-key="' + result.response.addToList.list + '"]'), result.response.addToList.close, result.response.addToList.replace);
};