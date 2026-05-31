
function previewFile() {
    const file = document.getElementById('imageInput').files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewImage').style.display = 'block';
            document.getElementById('placeholder').style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
}
