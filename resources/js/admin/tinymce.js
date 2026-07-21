import tinymce from 'tinymce';

import 'tinymce/icons/default/icons.min.js';
import 'tinymce/themes/silver/theme.min.js';
import 'tinymce/models/dom/model.min.js';

import 'tinymce/skins/ui/oxide/skin.js';
import 'tinymce/skins/ui/oxide/content.js';
import 'tinymce/skins/content/default/content.js';

import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/table';

const syncedForms = new WeakSet();

const syncEditorsBeforeSubmit = () => {
    document.querySelectorAll('.tinymce-editor').forEach((textarea) => {
        const form = textarea.closest('form');

        if (!form || syncedForms.has(form)) {
            return;
        }

        form.addEventListener('submit', () => {
            tinymce.triggerSave();
        });

        syncedForms.add(form);
    });
};

export const initTinyMceEditors = () => {
    if (!document.querySelector('.tinymce-editor')) {
        return;
    }

    tinymce.init({
        selector: '.tinymce-editor',
        license_key: 'gpl',
        height: 560,
        menubar: 'edit view insert format table tools',
        promotion: false,
        branding: false,
        plugins: 'advlist autolink code fullscreen link lists table',
        toolbar: [
            'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify',
            'bullist numlist outdent indent | link table | removeformat code fullscreen',
        ].join(' | '),
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Quote=blockquote',
        table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
        content_style: [
            'body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.7; color: #111827; }',
            'h2, h3, h4 { font-weight: 700; line-height: 1.3; margin: 1.2em 0 .6em; }',
            'h2 { font-size: 1.5rem; } h3 { font-size: 1.25rem; } h4 { font-size: 1.125rem; }',
            'p { margin: 0 0 1em; }',
            'table { border-collapse: collapse; width: 100%; }',
            'td, th { border: 1px solid #d1d5db; padding: .5rem; }',
        ].join(' '),
        setup: (editor) => {
            editor.on('change keyup undo redo', () => {
                editor.save();
            });
        },
    });

    syncEditorsBeforeSubmit();
};
