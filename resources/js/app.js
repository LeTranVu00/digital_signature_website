import 'flowbite';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('.tinymce-editor')) {
        return;
    }

    import('./admin/tinymce')
        .then(({ initTinyMceEditors }) => initTinyMceEditors());
});
