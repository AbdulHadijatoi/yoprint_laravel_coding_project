<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CSV Upload Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body {
            background-color: #f7fafc;
        }

        main {
            max-width: 960px;
            margin: 2rem auto;
        }

        .drop-zone {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background-color: #fff;
            transition: border-color 0.2s ease, background-color 0.2s ease;
            cursor: pointer;
        }

        .drop-zone.dragover {
            border-color: #3182ce;
            background-color: #ebf8ff;
        }

        .upload-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        table {
            width: 100%;
        }

        td.status {
            text-transform: capitalize;
            font-weight: 600;
        }

        td.status[data-status="failed"] {
            color: #c53030;
        }

        td.status[data-status="completed"] {
            color: #2f855a;
        }

        td.status[data-status="processing"],
        td.status[data-status="pending"] {
            color: #dd6b20;
        }

        .feedback {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<nav class="container-fluid">
    <ul>
        <li><strong>Product CSV Uploads</strong></li>
    </ul>
    <ul>
        <li><a href="#" id="refreshButton" role="button" class="secondary">Refresh</a></li>
    </ul>
</nav>
<main class="container">
    @if(session('status'))
        <article class="feedback" data-status="success">
            {{ session('status') }}
        </article>
    @endif
    @if ($errors->any())
        <article class="feedback" data-status="error">
            <strong>Upload failed:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    <section>
        <hgroup>
            <h2>Upload CSV File</h2>
            <p>Select a CSV/TSV file containing product data. You can also drag &amp; drop the file below.</p>
        </hgroup>
        <form id="uploadForm" action="{{ route('product-uploads.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" id="fileInput" name="file" accept=".csv,.tsv,.txt" required hidden>
            <label for="fileInput" class="drop-zone" id="dropZone">
                <p id="dropZoneText">Select file / Drag and drop</p>
                <small id="selectedFileName"></small>
            </label>
            <div class="upload-actions">
                <button type="submit" id="uploadButton">Upload File</button>
            </div>
        </form>
    </section>

    <section>
        <hgroup>
            <h2>Recent Uploads</h2>
            <p>Statuses update automatically every few seconds.</p>
        </hgroup>
        <div class="table-container">
            <table id="uploadsTable">
                <thead>
                <tr>
                    <th scope="col">Time</th>
                    <th scope="col">File Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Processed</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 2rem;" id="emptyState">
                        No uploads yet. Start by uploading a CSV file.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    const uploadsEndpoint = "{{ route('product-uploads.index') }}";
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const dropZoneText = document.getElementById('dropZoneText');
    const selectedFileName = document.getElementById('selectedFileName');
    const uploadsTableBody = document.querySelector('#uploadsTable tbody');
    const emptyState = document.getElementById('emptyState');
    const refreshButton = document.getElementById('refreshButton');
    let pollTimer = null;

    function humanizeStatus(status) {
        return status ? status.charAt(0).toUpperCase() + status.slice(1) : '';
    }

    function formatTimestamp(timestamp, fallback = '') {
        if (!timestamp) {
            return fallback;
        }

        const date = new Date(timestamp);
        if (Number.isNaN(date.getTime())) {
            return fallback;
        }

        return date.toLocaleString();
    }

    function renderUploads(rows) {
        uploadsTableBody.innerHTML = '';

        if (!rows.length) {
            if (emptyState) {
                uploadsTableBody.appendChild(emptyState);
            }
            return;
        }

        rows.forEach((row) => {
            const tr = document.createElement('tr');

            const uploadedCell = document.createElement('td');
            uploadedCell.innerHTML = `${formatTimestamp(row.created_at)}<br><small>${row.created_at_human || ''}</small>`;

            const nameCell = document.createElement('td');
            if (row.file_url) {
                const link = document.createElement('a');
                link.href = row.file_url;
                link.textContent = row.file_name || 'Download';
                link.target = '_blank';
                nameCell.appendChild(link);
            } else {
                nameCell.textContent = row.file_name || '—';
            }

            const statusCell = document.createElement('td');
            statusCell.classList.add('status');
            statusCell.dataset.status = row.status || 'pending';
            statusCell.textContent = humanizeStatus(row.status);
            if (row.error_message) {
                const error = document.createElement('div');
                error.style.fontSize = '0.75rem';
                error.style.color = '#c53030';
                error.textContent = row.error_message;
                statusCell.appendChild(error);
            }

            const countsCell = document.createElement('td');
            countsCell.innerHTML = `${row.processed_rows}/${row.total_rows || 0}`;

            tr.appendChild(uploadedCell);
            tr.appendChild(nameCell);
            tr.appendChild(statusCell);
            tr.appendChild(countsCell);

            uploadsTableBody.appendChild(tr);
        });
    }

    async function fetchUploads() {
        try {
            const response = await fetch(uploadsEndpoint, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Unable to load uploads.');
            }

            const data = await response.json();
            const rows = Array.isArray(data.data) ? data.data : [];
            renderUploads(rows);
        } catch (error) {
            console.error(error);
        }
    }

    function startPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
        }

        pollTimer = setInterval(fetchUploads, 5000);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    uploadForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!fileInput.files.length) {
            alert('Please select a file to upload.');
            return;
        }

        const formData = new FormData(uploadForm);
        const submitButton = document.getElementById('uploadButton');
        submitButton.disabled = true;
        submitButton.textContent = 'Uploading...';

        try {
            const response = await fetch(uploadForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                const errorPayload = await response.json().catch(() => ({}));
                const message = errorPayload.message || 'Upload failed. Please check the file and try again.';
                alert(message);
                return;
            }

            fileInput.value = '';
            selectedFileName.textContent = '';
            dropZoneText.textContent = 'Select file / Drag and drop';
            await fetchUploads();
        } catch (error) {
            console.error(error);
            alert('Upload failed. Please try again.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Upload File';
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            selectedFileName.textContent = fileInput.files[0].name;
            dropZoneText.textContent = 'File ready to upload';
        } else {
            selectedFileName.textContent = '';
            dropZoneText.textContent = 'Select file / Drag and drop';
        }
    });

    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropZone.classList.remove('dragover');

        if (event.dataTransfer.files.length) {
            fileInput.files = event.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    refreshButton.addEventListener('click', (event) => {
        event.preventDefault();
        fetchUploads();
    });

    fetchUploads().then(startPolling);
    window.addEventListener('beforeunload', stopPolling);
</script>
</body>
</html>

