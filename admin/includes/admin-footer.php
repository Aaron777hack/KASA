        </div><!-- page-content -->
    </div><!-- main-content -->
</div><!-- admin-wrapper -->

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- jQuery (pour la gestion des images preview) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
// Prévisualiser l'image avant upload
$(document).on('change', 'input[type=file][data-preview]', function() {
    var file = this.files[0];
    var previewId = $(this).data('preview');
    if (file && file.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#' + previewId).attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
    }
});

// Confirmation de suppression
$(document).on('click', '.btn-delete', function(e) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
        e.preventDefault();
    }
});

// Auto-générer le slug depuis le titre
$('#title, #name').on('input', function() {
    var slug = $(this).val()
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    $('#slug').val(slug);
});
</script>
</body>
</html>
