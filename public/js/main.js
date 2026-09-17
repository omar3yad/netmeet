let cropper;
const modelImage = document.getElementById('previewImage');
const modal = $('#previewModal');

//add headers to all the ajax requests
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

//show success toaster
function showSuccess(message) {
    toastr.success(message);
}

//show warning toaster
function showInfo(message) {
    toastr.info(message);
}

//show error toaster
function showError(message) {
    toastr.error(message || languages.error_occurred);
}

//ajax call to check if the meeting exist or not
$("#meeting").on("submit", function (e) {
    e.preventDefault();

    if (conferenceId.value.length <= 3) {
        showError(languages.no_meeting);
        return;
    }

    $("#initiate").attr("disabled", true);

    $.ajax({
        url: "check-meeting",
        data: $(this).serialize(),
        type: "post",
    })
        .done(function (data) {
            data = JSON.parse(data);
            $("#initiate").attr("disabled", false);

            if (data.success) {
                location.href = "meeting/" + data.id;
            } else {
                showError(languages.no_meeting);
            }
        })
        .catch(function () {
            showError(languages.no_meeting);
            $("#initiate").attr("disabled", false);
        });
});

//dynamically add google analytics tracking ID
if (googleAnalyticsTrackingId !== "null" && googleAnalyticsTrackingId) {
    let script = document.createElement("script");
    script.src =
        "https://www.googletagmanager.com/gtag/js?id=" +
        googleAnalyticsTrackingId;
    document.body.appendChild(script);

    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag("js", new Date());
    gtag("config", googleAnalyticsTrackingId);
}

//check if the cookie is accepted
if (cookieConsent == "enabled" && !localStorage.getItem("cookieAccepted")) {
    setTimeout(function () {
        $(".cookie").addClass("show-cookie");
    }, 3000);
}

//store in the local storage and hide the cookie dialogue
$(document).on("click", ".confirm-cookie", function () {
    localStorage.setItem("cookieAccepted", true);
    $(".cookie").removeClass("show-cookie");
});

//scroll to top
$('.start-btn').on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 'slow');
});

//set href into the social links
$('#fbShare').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + location.hostname + '&quote=' + socialInvitation);
$('#twitterShare').attr('href', 'https://twitter.com/share?url=' + location.hostname + '&text=' + socialInvitation);
$('#waShare').attr('href', 'https://api.whatsapp.com/send?text=' + socialInvitation + ' \n ' + location.hostname);

//auto login feature for demo mode
$("#autoLogin").on('change', function () {
    let email;

    if (this.value == "admin") {
        email = "admin@thertclabs.com";
    } else if (this.value == "user_1") {
        email = "user1@thertclabs.com";
    } else if (this.value == "user_2") {
        email = "user2@thertclabs.com";
    }

    $("#loginButton").attr('disabled', true);

    $("#email").val(email);
    $("#password").val('123456');
    $("#login").trigger('submit');
});

//to prevent XSS vulnerability
function htmlEscape(input) {
    return input
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

//make sure this document is always at top due to media permission
if (window != top) top.location.href = window.location.href;

//handle remove copoun
$("#remove_coupon").on("click", function (e) {
    $('input[name="coupon"]').val('');
    //remove the submit button
    document.querySelector('#form-payment').submit.remove();

    //submit the form
    document.querySelector('#form-payment').submit();
})

//update summary
let updateSummary = (type) => {
    if (type == 'month') {
        document.querySelectorAll('.checkout-month').forEach(function (element) {
            element.classList.add('d-inline-block');
        });
        document.querySelectorAll('.checkout-year').forEach(function (element) {
            element.classList.remove('d-inline-block');
        });
    } else {
        document.querySelectorAll('.checkout-month').forEach(function (element) {
            element.classList.remove('d-inline-block');
        });
        document.querySelectorAll('.checkout-year').forEach(function (element) {
            element.classList.add('d-inline-block');
        });
    }
};

//update billing type
let updateBillingType = (value) => {
    document.querySelectorAll('.checkout-subscription').forEach(function (element) {
        element.classList.remove('d-none');
    });
    document.querySelectorAll('.checkout-subscription').forEach(function (element) {
        element.classList.add('d-block');
    });
}

//payment form
if (document.querySelector('#form-payment')) {
    let url = new URL(window.location.href);

    document.querySelectorAll('[name="interval"]').forEach(function (element) {
        if (element.checked) {
            updateSummary(element.value);
        }

        //listen to interval changes
        element.addEventListener('change', function () {
            url.searchParams.set('interval', element.value);
            history.pushState(null, null, url.href);
            updateSummary(element.value);
        });
    });

    document.querySelectorAll('[name="payment_gateway"]').forEach(function (element) {
        if (element.checked) {
            updateBillingType(element.value);
        }

        //listen to payment gateway changes
        element.addEventListener('change', function () {
            url.searchParams.set('payment', element.value);
            history.pushState(null, null, url.href);
            updateBillingType(element.value);
        });
    });

    //if the Add a coupon button is clicked
    document.querySelector('#coupon') && document.querySelector('#coupon').addEventListener('click', function (e) {
        e.preventDefault();

        this.classList.add('d-none');
        document.querySelector('#coupon-input').classList.remove('d-none');
        document.querySelector('input[name="coupon"]').removeAttribute('disabled');
    });

    //if the Cancel coupon button is clicked
    document.querySelector('#coupon-cancel') && document.querySelector('#coupon-cancel').addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector('#coupon').classList.remove('d-none');
        document.querySelector('#coupon-input').classList.add('d-none');
        document.querySelector('input[name="coupon"]').setAttribute('disabled', 'disabled');
    });

    //if the country value changes
    document.querySelector('#i-country').addEventListener('change', function () {
        document.querySelector('#form-payment').submit.remove();
        document.querySelector('#form-payment').submit();
    });
}

//handle plan month click
document.querySelector('#plan-month') && document.querySelector('#plan-month').addEventListener("click", function () {
    document.querySelectorAll('.plan-month').forEach(element => element.classList.add('d-block'));
    document.querySelectorAll('.plan-year').forEach(element => element.classList.remove('d-block'));
});

//handle plan year click
document.querySelector('#plan-year') && document.querySelector('#plan-year').addEventListener("click", function () {
    document.querySelectorAll('.plan-year').forEach(element => element.classList.add('d-block'));
    document.querySelectorAll('.plan-month').forEach(element => element.classList.remove('d-block', 'plan-preload'));
});

//copy api token to the clipboard
$("#copyApiToken").on('click', function () {
    let inp = document.createElement('textarea');
    inp.style.display = 'hidden';
    document.body.appendChild(inp);
    inp.value = api_token.value;
    inp.select();
    document.execCommand('copy', false);
    inp.remove();
    showSuccess(languages.token_copied);
});

//ajax call to delete contact
$(".delete-contact").on("click", function () {
    if (!confirm(languages.confirmation)) return;
    let currentRow = $(this);
    currentRow.attr("disabled", true);

    let deleteUrl = $(this).attr('data-url');

    let form = new FormData();
    form.append("id", currentRow.data("id"));

    $.ajax({
        url: deleteUrl,
        data: form,
        type: "post",
        cache: false,
        contentType: false,
        processData: false,
    })
        .done(function (data) {
            data = JSON.parse(data);

            if (data.success) {
                currentRow.parent().parent().remove();
                showSuccess(data.message);
            } else {
                showError(data.error);
            }
        })
        .catch(function () {
            currentRow.attr("disabled", false);
            showError();
        });
});

//listen on avatar remove event
$('#removeAvatar').on('click', function () {
    if (!confirm(languages.confirmation)) return;

    $(this).attr('disabled', true);

    $.ajax({
        url: "delete-avatar",
        data: {
            id: $('#userid').val()
        },
        type: "post",
    })
        .done(function (data) {
            data = JSON.parse(data);

            if (data.success) {
                window.location.reload();
            } else {
                showError(data.error);
            }

            $('#removeAvatar').attr('disabled', false);
        })
        .catch(function () {
            $('#removeAvatar').attr('disabled', false);
            showError(languages.error_occurred);
        });
});

//hide alerts
setTimeout(() => {
    $(".alert").hide(1000);
}, 3000);

//handle avatar change
$(document).on("change", "#avatarchange", function (e) {
    var files = e.target.files;
    if (files && files.length > 0) {
        var url = URL.createObjectURL(files[0]);
        modelImage.src = url;
        modal.modal('show');
    }
});

//handle avatar preview modal toggle
modal.on('shown.bs.modal', function () {
    cropper = new Cropper(modelImage, {
        aspectRatio: 1,
        viewMode: 2,
        responsive: true,
        minContainerWidth: 465,
        minContainerHeight: 200,
        minCanvasWidth: 465,
        minCanvasHeight: 200,
        minCropBoxWidth: 465,
        minCropBoxHeight: 200,
        preview: '.preview'
    });
}).on('hidden.bs.modal', function () {
    cropper.destroy();
    cropper = null;
});

//crop and upload avatar
$("#crop_button").on('click', function () {
    const imgurl = cropper.getCroppedCanvas().toDataURL();

    $(this).attr('disabled', true);

    cropper.getCroppedCanvas().toBlob((blob) => {
        const formData = new FormData();
        formData.append('image', blob);
        formData.append('extension', blob.type.replace("image/", " "));
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "upload-avatar",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    showSuccess(data.success);
                    modal.modal('hide');
                    $('#imagePreview').attr('src', imgurl).show();
                    $('#profileDropdown').html('<img src="' + imgurl + '" class="user-avatar">');
                    $('#initial').hide();
                } else {
                    showError(data.error);
                }
            },
            complete: function () {
                $("#crop_button").attr('disabled', true);
            }
        });
    })
});

//PWA
// Universal PWA Installation Handler
if (pwa == 'enabled') {
    let deferredPrompt;
    let installationSupported = false;

    // Detect device and browser type
    function getDeviceInfo() {
        const userAgent = navigator.userAgent;
        const isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;
        const isAndroid = /Android/.test(userAgent);
        const isChrome = /Chrome/.test(userAgent) && !/Edge/.test(userAgent);
        const isSafari = /Safari/.test(userAgent) && !/Chrome/.test(userAgent);
        const isFirefox = /Firefox/.test(userAgent);
        const isEdge = /Edge/.test(userAgent);
        const isMobile = /Mobi|Android/i.test(userAgent);

        return { isIOS, isAndroid, isChrome, isSafari, isFirefox, isEdge, isMobile };
    }

    // Check if PWA is already installed
    function isPWAInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }

    // Handle Chrome/Edge beforeinstallprompt event
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        deferredPrompt = e;

    });

    // Handle app installed event
    window.addEventListener('appinstalled', (e) => {
        localStorage.setItem("pwaInstalled", "true");
        showSuccess("App installed successfully!");
    });

    // Show installation modal with device-specific instructions
    function showInstallModal(type = 'manual') {
        const deviceInfo = getDeviceInfo();
        let modalContent = '';

        if (type === 'native' && deferredPrompt) {
            // Native installation prompt available
            modalContent = `
                <div class="modal-header">
                    <h5 class="modal-title">Install Web Application</h5>
                </div>
                <div class="modal-body">
                    <p>Install shortcut on your device for easy access in the future.</p>
                </div>
                <div class="modal-footer">
                    <button id="closeInstallationModal" type="button" class="btn btn-light btn-sm">
                        No, Thanks
                    </button>
                    <button id="installApp" type="button" class="btn btn-primary btn-sm ms-2">
                        Install
                    </button>
                </div>
            `;
        } else {
            // Manual installation instructions
            modalContent = getManualInstallContent(deviceInfo);
        }

        document.getElementById('installationModal').querySelector('.modal-content').innerHTML = modalContent;
        $("#installationModal").modal("show");

        // Bind event listeners
        bindInstallationEvents(type, deviceInfo);
    }

    // Get manual installation instructions based on device
    function getManualInstallContent(deviceInfo) {
        let instructions = '';
        let title = 'Install Web Application';

        if (deviceInfo.isIOS) {
            if (deviceInfo.isSafari) {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>To install this app on your iOS device:</h6>
                    <ol class="install-steps">
                        <li>Tap the <strong>Share</strong> button <i class="fas fa-share"></i> at the bottom of Safari</li>
                        <li>Scroll down and tap <strong>"Add to Home Screen"</strong> <i class="fas fa-plus-square"></i></li>
                        <li>Tap <strong>"Add"</strong> in the top right corner</li>
                    </ol>
                    <div class="alert alert-info mt-3">
                        <small><i class="fas fa-info-circle"></i> This feature only works in Safari browser</small>
                    </div>
                `;
            } else {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fas fa-safari fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>To install this app:</h6>
                    <p>Please open this website in <strong>Safari</strong> browser to install it as an app.</p>
                    <div class="text-center">
                        <button id="openInSafari" class="btn btn-primary btn-sm">
                            Open in Safari
                        </button>
                    </div>
                `;
            }
        } else if (deviceInfo.isAndroid) {
            if (deviceInfo.isChrome) {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fab fa-chrome fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>To install this app on Android:</h6>
                    <ol class="install-steps">
                        <li>Tap the <strong>menu</strong> button <i class="fas fa-ellipsis-v"></i> (three dots)</li>
                        <li>Tap <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                        <li>Tap <strong>"Install"</strong> or <strong>"Add"</strong></li>
                    </ol>
                `;
            } else if (deviceInfo.isFirefox) {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fab fa-firefox fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>To install this app on Android Firefox:</h6>
                    <ol class="install-steps">
                        <li>Tap the <strong>menu</strong> button <i class="fas fa-bars"></i></li>
                        <li>Tap <strong>"Install"</strong> or <strong>"Add to Home Screen"</strong></li>
                        <li>Confirm the installation</li>
                    </ol>
                `;
            } else {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fab fa-chrome fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>To install this app:</h6>
                    <p>Please open this website in <strong>Chrome</strong> browser for the best installation experience.</p>
                    <div class="text-center">
                        <button id="openInChrome" class="btn btn-primary btn-sm">
                            Open in Chrome
                        </button>
                    </div>
                `;
            }
        } else {
            // Desktop
            if (deviceInfo.isChrome || deviceInfo.isEdge) {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fas fa-desktop fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>To install this app on your computer:</h6>
                    <ol class="install-steps">
                        <li>Look for the <strong>install</strong> icon <i class="fas fa-download"></i> in the address bar</li>
                        <li>Or go to the browser menu <i class="fas fa-ellipsis-v"></i> → <strong>"Install [App Name]"</strong></li>
                        <li>Click <strong>"Install"</strong></li>
                    </ol>
                `;
            } else {
                instructions = `
                    <div class="text-center mb-3">
                        <i class="fas fa-bookmark fa-3x text-primary mb-2"></i>
                    </div>
                    <h6>Bookmark this app:</h6>
                    <p>While your browser doesn't support app installation, you can bookmark this page for quick access.</p>
                    <div class="text-center">
                        <button id="bookmarkApp" class="btn btn-primary btn-sm">
                            Bookmark This Page
                        </button>
                    </div>
                `;
            }
        }

        return `
            <div class="modal-header">
                <h5 class="modal-title">${title}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ${instructions}
            </div>
            <div class="modal-footer">
                <button id="closeInstallationModal" type="button" class="btn btn-light btn-sm">
                    Close
                </button>
                <button id="dontShowAgain" type="button" class="btn btn-outline-secondary btn-sm">
                    Don't show again
                </button>
            </div>
        `;
    }

    // Bind installation event listeners
    function bindInstallationEvents(type, deviceInfo) {
        // Native installation
        document.getElementById("installApp")?.addEventListener("click", () => {
            $("#installationModal").modal("hide");
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        showSuccess("App installation started!");
                    }
                    deferredPrompt = null;
                });
            }
        });

        // Close modal
        document.getElementById("closeInstallationModal")?.addEventListener("click", () => {
            $("#installationModal").modal("hide");
        });

        // Don't show again
        document.getElementById("dontShowAgain")?.addEventListener("click", () => {
            localStorage.setItem("dontWantToInstall", "true");
            $("#installationModal").modal("hide");
        });

        // Open in Safari (iOS)
        document.getElementById("openInSafari")?.addEventListener("click", () => {
            const safariUrl = `x-web-search://?${encodeURIComponent(window.location.href)}`;
            window.location.href = safariUrl;
        });

        // Open in Chrome (Android)
        document.getElementById("openInChrome")?.addEventListener("click", () => {
            const chromeUrl = `googlechrome://${window.location.href}`;
            window.location.href = chromeUrl;
        });

        // Bookmark functionality
        document.getElementById("bookmarkApp")?.addEventListener("click", () => {
            if (window.sidebar && window.sidebar.addPanel) {
                // Firefox
                window.sidebar.addPanel(document.title, window.location.href, '');
            } else if (window.external && ('AddFavorite' in window.external)) {
                // IE
                window.external.AddFavorite(window.location.href, document.title);
            } else {
                // Other browsers
                alert('Please bookmark this page manually: Press Ctrl+D (or Cmd+D on Mac)');
            }
            $("#installationModal").modal("hide");
        });
    }

    // Check for installation prompt periodically
    // function checkForInstallPrompt() {
    //     if (!installationSupported && !localStorage.getItem("dontWantToInstall") && !isPWAInstalled()) {
    //         const deviceInfo = getDeviceInfo();

    //         // Show manual installation instructions for supported devices
    //         if (deviceInfo.isIOS || deviceInfo.isAndroid ||
    //             (deviceInfo.isChrome || deviceInfo.isEdge)) {
    //             setTimeout(() => {
    //                 showInstallModal('manual');
    //             }, 3000);
    //         }
    //     }
    // }

    // Initialize installation check
    // setTimeout(checkForInstallPrompt, 5000);

    // Add install button to UI (optional)
    function addInstallButton() {
        const installButton = document.createElement('button');
        installButton.id = 'pwa-install-btn';
        installButton.className = 'btn btn-outline-primary btn-sm d-none';
        installButton.innerHTML = '<i class="fas fa-download"></i> Install App';
        installButton.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;

        installButton.addEventListener('click', () => {
            if (deferredPrompt) {
                showInstallModal('native');
            } else {
                showInstallModal('manual');
            }
        });

        document.body.appendChild(installButton);

        // Show button if installation is possible
        if (!isPWAInstalled()) {
            setTimeout(() => {
                installButton.classList.remove('d-none');
            }, 80000);
        }
    }

    // Add install button to page

    // Hide install button if PWA is already installed
    if (isPWAInstalled()) {
        document.getElementById('pwa-install-btn')?.classList.add('d-none');
    }
}
