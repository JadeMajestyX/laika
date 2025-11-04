<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: linear-gradient(90deg,#3A7CA5,#2f6485);">
                    <h3 class="mb-0 d-flex align-items-center"><i class="bi bi-heart-pulse-fill me-2"></i> {{ __('Información del perfil') }}</h3>
                    <small class="text-white-50">{{ __("Actualiza la información del perfil de tu cuenta.") }}</small>
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
                                <input id="nombre" name="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $user->nombre) }}" required autofocus autocomplete="nombre">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="apellido_paterno" class="form-label">{{ __('Apellido Paterno') }}</label>
                                <input id="apellido_paterno" name="apellido_paterno" type="text" class="form-control @error('apellido_paterno') is-invalid @enderror" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" required autocomplete="apellido_paterno">
                                @error('apellido_paterno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="apellido_materno" class="form-label">{{ __('Apellido Materno') }}</label>
                                <input id="apellido_materno" name="apellido_materno" type="text" class="form-control @error('apellido_materno') is-invalid @enderror" value="{{ old('apellido_materno', $user->apellido_materno) }}" required autocomplete="apellido_materno">
                                @error('apellido_materno')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email: mostrado como readonly para referencia --}}
                            <div class="col-md-6">
                                <label for="email" class="form-label">{{ __('Correo electrónico') }}</label>
                                <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled>
                            </div>

                            <div class="col-12 d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>

                                @if (session('status') === 'profile-updated')
                                    <div class="alert alert-success mb-0 small ms-2" role="status">{{ __('Guardado.') }}</div>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
