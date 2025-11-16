<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Notificaciones | Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body{ background:#f8f9fa; font-family:Inter,system-ui,sans-serif; }
    </style>
</head>
<body>
<div class="container py-4">
    <h3 class="mb-4">Enviar Notificación (Test)</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('test.noti.send') }}" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label">Modo destino</label>
                    <div class="d-flex gap-3">
                        <div class="form-check"><input class="form-check-input" type="radio" name="mode" value="user" id="mUser" checked><label for="mUser" class="form-check-label">Usuario</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="mode" value="token" id="mToken"><label for="mToken" class="form-check-label">Token</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="mode" value="topic" id="mTopic"><label for="mTopic" class="form-check-label">Topic</label></div>
                    </div>
                </div>
                <div class="col-md-4 mode-user">
                    <label class="form-label" for="selUser">Usuario</label>
                    <select id="selUser" name="user_id" class="form-select">
                        <option value="">-- seleccionar --</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->id }}">{{ $u->email }} ({{ $u->nombre }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 mode-token d-none">
                    <label class="form-label" for="selToken">Token dispositivo</label>
                    <select id="selToken" name="token" class="form-select">
                        <option value="">-- seleccionar --</option>
                        @foreach($tokens as $t)
                            <option value="{{ $t->token }}">{{ Str::limit($t->token, 42) }} | {{ $t->user->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mode-topic d-none">
                    <label class="form-label" for="topic">Topic</label>
                    <input class="form-control" id="topic" name="topic" placeholder="all">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="title">Título</label>
                    <input type="text" class="form-control" id="title" name="title" required maxlength="150">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="body">Mensaje</label>
                    <input type="text" class="form-control" id="body" name="body" required maxlength="500">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="screen">Pantalla destino (data.screen)</label>
                    <input type="text" class="form-control" id="screen" name="screen" placeholder="citas">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="extra_id">ID extra (data.id)</label>
                    <input type="text" class="form-control" id="extra_id" name="extra_id" placeholder="42">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Enviar</button>
                    <a href="{{ route('test.noti') }}" class="btn btn-outline-secondary">Recargar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header"><strong>Tokens recientes (max 300)</strong></div>
        <div class="table-responsive" style="max-height:360px;">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Usuario</th><th>Token (corto)</th><th>Platform</th><th>Last Seen</th></tr></thead>
                <tbody>
                @forelse($tokens as $t)
                    <tr>
                        <td>{{ $t->user->email }}</td>
                        <td><code>{{ Str::limit($t->token, 50) }}</code></td>
                        <td>{{ $t->platform ?? '-' }}</td>
                        <td>{{ $t->last_seen_at ? $t->last_seen_at->diffForHumans() : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">Sin tokens</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', ()=>{
    const modeRadios = document.querySelectorAll('input[name="mode"]');
    function sync(){
        const val = document.querySelector('input[name="mode"]:checked')?.value;
        document.querySelectorAll('.mode-user,.mode-token,.mode-topic').forEach(el=> el.classList.add('d-none'));
        if(val==='user') document.querySelectorAll('.mode-user').forEach(el=> el.classList.remove('d-none'));
        else if(val==='token') document.querySelectorAll('.mode-token').forEach(el=> el.classList.remove('d-none'));
        else if(val==='topic') document.querySelectorAll('.mode-topic').forEach(el=> el.classList.remove('d-none'));
    }
    modeRadios.forEach(r=> r.addEventListener('change', sync));
    sync();
});
</script>
</body>
</html>