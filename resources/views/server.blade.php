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
                    <form method="post">
                        <div class="mb-3">
                            <label for="serverUrl" class="form-label">Server URL</label>
                            <input type="url" class="form-control" id="serverUrl" required>
                        </div>

                        <div class="mb-3">
                            <label for="serverPort" class="form-label">Server port</label>
                            <input type="number" class="form-control" id="serverPort" placeholder="32400" required>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-auto">
                              <label for="serverToken" class="form-label">Server token</label>
                                <input type="email" class="form-control" id="serverToken" required>
                            </div>
                            <div class="col-auto">
                                <button class="btn mt-4 btn-info" data-bs-toggle="modal" data-bs-target="#serverTokenModal">I don't know my token</button>
                            </div>
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
    <div class="modal" tabindex="-1" id="serverTokenModal" x-data="modalComponent">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Get token for my server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <template x-if="showError">
                        <div class="alert alert-danger" x-text="message"></div>
                    </template>
                    <template x-if="showSuccess">
                        <div class="alert alert-success">Your token has been correctly retrieved.  You can now close this modal</div>
                    </template>

                    <form @submit.prevent="submitData">
                        <div class="mb-3 row">
                            <label for="email" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" x-model="formData.email" required autofocus>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-3 col-form-label">Password</label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" id="password" name="email" value="" x-model="formData.password" required>
                            </div>
                        </div>
                        <p class="italic">
                            Your email and password will <span class="font-bold">only</span> be used to get your token and won't be stored.
                        </p>
                        <div class="mb-3">
                            <input type="submit" class="btn btn-success" value="Get my token">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @push('javascript')
        <script type="application/javascript" async defer>
            document.addEventListener('alpine:init', () => {
                Alpine.data('modalComponent', () => ({
                    showError: false,
                    showSuccess: false,
                    message: '',
                    formData: {
                        email: '',
                        password: '',
                    },

                    async submitData() {
                        fetch('/api/token', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                'email': this.formData.email,
                                'password': this.formData.password,
                                '_token': '{{ csrf_token() }}',
                            })
                        }).then(response => response.json())
                            .then((response) => {
                                if (!response.token)
                                {
                                    this.showError = true;
                                    this.message = response.error;
                                } else {
                                    this.showSuccess = true;
                                    document.querySelector('#serverToken').value = response.token;
                                }
                            })
                            .catch((e) => {
                                this.message = 'Ooops! Something went wrong!'
                            })
                    }
                }))
            });
        </script>
    @endpush
</x-app-layout>
