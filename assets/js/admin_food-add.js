
    function previewFile() {
        const file = document.getElementById('imageInput').files[0];
        const preview = document.getElementById('previewImage');
        const placeholder = document.getElementById('uploadPlaceholder');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
            placeholder.style.display = 'flex';
        }
    }

    const uploadBox = document.getElementById('imageUploadBox');
    const fileInput = document.getElementById('imageInput');

    uploadBox.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#1a73e8';
        this.style.background = '#f0f6ff';
    });

    uploadBox.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#e2e8f0';
        this.style.background = '#f8fafc';
    });

    uploadBox.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#e2e8f0';
        this.style.background = '#f8fafc';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            previewFile();
        }
    });
