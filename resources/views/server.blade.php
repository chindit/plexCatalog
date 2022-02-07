<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My server') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="post" x-data="getToken()">
                        <div class="mb-3">
                            <label for="serverUrl" class="form-label">Server URL</label>
                            <input type="url" class="form-control" id="serverUrl" x-model="formData.url" required>
                        </div>

                        <div class="mb-3">
                            <label for="serverPort" class="form-label">Email address</label>
                            <input type="number" class="form-control" id="serverPort" placeholder="32400" x-model="formData.port" required>
                        </div>

                        <div class="mb-3">
                            <label for="serverToken" class="form-label">Server token</label>
                            <input type="email" class="form-control" id="serverToken" required>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serverTokenModal">I don't know my token</button>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-3">Create server</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- New server modal -->
    <div class="modal" tabindex="-1" id="serverTokenModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Get token for my server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form x-data="getToken()" @submit.prevent="submitData">
                        <template x-if="invalidServerUrl">
                            <div class="alert alert-danger">Your server URL is invalid.  Please close this modal, update your server URL and try again</div>
                        </template>
                        <div class="mb-3">
                            <label for="serverPassword" class="form-label">Server password</label>
                            <input type="password" class="form-control" id="serverPassword" x-model="formData.password">
                        </div>
                        <p class="italic">
                            Your password will <span class="font-bold">only</span> be used to get your token and won't be stored.
                        </p>
                        <div class="mb-3">
                            <input type="submit" class="btn btn-success" value="Get my token">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    @push('javascript')
        <script>
            function getToken() {
                console.log('Oui?');
                return {
                    formData: {
                        url: '',
                        port: '',
                        password: ''
                    },
                    invalidServerUrl: false,

                    submitData() {
                        if (!this.isValidHttpUrl(this.formData.url)) {
                            this.invalidServerUrl = true;
                            return;
                        }
                        this.message = ''
                        console.log(this.formData);
                        /*fetch('/contact', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.formData)
                        })
                            .then(() => {
                                this.message = 'Form sucessfully submitted!'
                            })
                            .catch(() => {
                                this.message = 'Ooops! Something went wrong!'
                            })*/
                    },

                    isValidHttpUrl(string) {
                        let url;

                        try {
                            url = new URL(string);
                        } catch (_) {
                            return false;
                        }

                        return url.protocol === "http:" || url.protocol === "https:";
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
