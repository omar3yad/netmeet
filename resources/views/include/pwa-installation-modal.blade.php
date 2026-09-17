<!-- Modal :: installation modal -->
<div class="modal fade" id="installationModal" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content";>
            <div class="modal-header">
                <h5 class="modal-title">{{__('Install Web Application')}}</h5>
            </div>
            <div class="modal-body">
                {{__('Install shortcut on desktop to easy use in future')}}
            </div>
            <div class="modal-footer">
                <button id="closeInstallationModal" type="button" class="btn btn-light btn-sm">
                    {{ __('No, Thanks') }}
                </button>
                <button id="installApp" type="button" class="btn btn-primary btn-sm ms-2">
                    {{ __('Install') }}
                </button>
            </div>
        </div>
    </div>
</div>