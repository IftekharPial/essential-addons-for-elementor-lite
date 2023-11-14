const SherePhotoViewer = function ($scope, $) {
    let sphereData = $scope.find('.eael-sphere-photo-wrapper').data('settings');

    if (sphereData.plugins !== undefined) {
        sphereData.plugins[0].unshift(PhotoSphereViewer.AutorotatePlugin);
    }

    const viewer = new PhotoSphereViewer.Viewer(sphereData);
};

jQuery(window).on("elementor/frontend/init", function () {
    elementorFrontend.hooks.addAction("frontend/element_ready/eael-sphere-photo-viewer.default", SherePhotoViewer);
});