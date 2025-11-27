<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Envio ZIP – Zadara</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Arial,Helvetica,sans-serif;margin:2rem;background:#f7f7f7}
h2{color:#003366}
form{margin-bottom:1.5rem}
input[type=file]{margin-right:.5rem}
button{padding:.4rem 1rem}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{padding:.6rem .8rem;border:1px solid #ddd;text-align:left;font-size:.9rem}
th{background:#003366;color:#fff}
.badge{font-size:.75rem;padding:.2rem .4rem;border-radius:3px;color:#fff}
.ok{background:#28a745}
.fail{background:#dc3545}
.btn-down{background:#007bff;color:#fff;padding:.3rem .6rem;border-radius:3px;text-decoration:none;font-size:.8rem}
.btn-down:hover{background:#0056b3}
#result{margin-top:1.5rem}
</style>
</head>
<body>
<h2>Envio ZIP para Zadara</h2>

// vou adicionar mais um comentário aqui
// Mais um comentário pra ver as diferenças

<form id="uploadForm" enctype="multipart/form-data">
@csrf
<input type="file" name="zip" accept=".zip" required>
<button type="submit">Enviar</button>
</form>
// adicionar um comentário aqui  só de onda
<table id="result" style="display:none">
<thead>
<tr>
<th>Arquivo</th>
<th>Status</th>
<th>Download</th>
</tr>
</thead>
<tbody></tbody>
</table>

<script>
const form = document.getElementById('uploadForm');
const table = document.querySelector('#result tbody');
const resultBox = document.getElementById('result');

form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);

        fetch('/api/zadara/upload-zip', {
method: 'POST',
body: fd,
headers: { 'Accept': 'text/html' }   // ← pede HTML
})
        .then(r => r.text())
        .then(html => {
            table.innerHTML = html;
            resultBox.style.display = 'table';
            });
        });
</script>
</body>
</html>
