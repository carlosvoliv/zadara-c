<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Upload ZIP → Zadara</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;margin:2rem;background:#f5f5f5}
h2{color:#333}
form{margin-bottom:1rem}
input[type=file]{margin:.5rem 0}
button{padding:.6rem 1.2rem;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer}
button:disabled{background:#6c757d}
#log{background:#fff;border:1px solid #ddd;padding:1rem;white-space:pre-wrap;max-height:400px;overflow:auto}
.ok{color:green}
.err{color:red}
</style>
</head>
<body>
<h2>Envio ZIP para Zadara</h2>

<form id="formZip" method="POST" enctype="multipart/form-data">
<input type="file" name="zip" accept=".zip" required>
<button type="submit" id="btnEnv">Enviar</button>
</form>

<pre id="log"></pre>

<script>
const log = (msg, cls='') => document.getElementById('log').textContent += (cls ? '['+cls+'] ' : '') + msg + '\n';
const btn   = document.getElementById('btnEnv');

document.getElementById('formZip').addEventListener('submit', async (e) => {
        e.preventDefault();
        btn.disabled = true;
        log('Iniciando upload...');

        const form = new FormData(e.target);

        try {
        const res = await fetch('/api/zadara/upload-zip', {method:'POST', body:form});
        const data = await res.json();

        if (!res.ok) throw new Error(data.message || 'Erro desconhecido');

        log('✅ Upload realizado! Total: ' + data.files.length + ' arquivo(s)', 'ok');
        data.files.forEach(f => log(`${f.original} → ${f.url}`, 'ok'));

        } catch (err) {
        console.error(err);
        log('❌ ' + err.message, 'err');
        log('Resposta cru: ' + await res.text(), 'err');
        btn.disabled = false;

        } finally {
            btn.disabled = false;
        }
}); // ← feche o addEventListener
</script>
</body>
</html>
