@extends('layouts.admin-master', ['title' => 'App Settings'])
@section('content')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 25px;
}

.switch input { display:none; }

.slider {
  position: absolute;
  cursor: pointer;
  background-color: #ccc;
  border-radius: 25px;
  top: 0; left: 0; right: 0; bottom: 0;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 19px;
  width: 19px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: .4s;
}

input:checked + .slider {
  background-color: #28a745;
}

input:checked + .slider:before {
  transform: translateX(25px);
}

</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5>Bots Activity</h5>
                <p class="text-muted mb-0">
                    Enable / Disable Fake Bot Interactions
                </p>
            </div>

            <div>
                <label class="switch">
                    <input type="checkbox" id="botsToggle"{{ $bots_enabled ? 'checked' : '' }}>
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('botsToggle').addEventListener('change', function () {
    fetch("{{ url('/settings/bots-toggle') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            bots_enabled: this.checked ? 1 : 0
        })
    })
    .then(res => res.json())
    .then(res => {
        alert(res.bots_enabled ? 'Bots Enabled ✅' : 'Bots Disabled ❌');
    });
});
</script>

@endsection
