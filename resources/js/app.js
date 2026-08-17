import 'flowbite';
import Alpine from 'alpinejs';
import { initComments } from './ui/comments';
import { initCommentVotes } from './ui/comment-votes';
import { initCounters } from './ui/counters';
import { initFormScrollRestoration, registerForms } from './ui/forms';
import { registerModal } from './ui/modal';
import { initQrCodes } from './ui/qr-code';
import { initScrollReveal } from './ui/reveal';
import { registerScrollNavigator } from './ui/scroll-navigator';
import { registerSidebar } from './ui/sidebar';
import { registerTheme } from './ui/theme';
import { registerToast } from './ui/toast';

window.Alpine = Alpine;

registerForms(Alpine);
registerModal(Alpine);
registerScrollNavigator(Alpine);
registerSidebar(Alpine);
registerTheme(Alpine);
registerToast(Alpine);
initComments();

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initCounters();
    initCommentVotes();
    initQrCodes();
    initFormScrollRestoration();

    if (document.querySelector('.tinymce-editor')) {
        import('./admin/tinymce')
            .then(({ initTinyMceEditors }) => initTinyMceEditors());
    }
});
