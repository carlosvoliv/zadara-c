@foreach($files as $f)
<tr>
    <td class="name">{{ basename($f['original']) }}</td>
    <td class="msg">
        @if(empty($f['error']))
            <span class="badge ok">✓ Enviado</span>
        @else
            <span class="badge fail">✗ {{ $f['error'] }}</span>
        @endif
    </td>
    <td class="down">
        @if(empty($f['error']))
            <a href="{{ $f['url'] }}" class="btn-down" download>
                Baixar
            </a>
        @endif
    </td>
</tr>
@endforeach
