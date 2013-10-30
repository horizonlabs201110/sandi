/**
 * Created with JetBrains WebStorm.
 * User: liuzhq
 * Date: 13-9-20
 * Time: 下午11:50
 * To change this template use File | Settings | File Templates.
 */
$(document).ready(function() {
    $('[data-toggle=offcanvas]').click(function() {
        $('.row-offcanvas').toggleClass('active');
    });
});