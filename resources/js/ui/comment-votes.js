export function initCommentVotes() {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-vote-button]');

        if (!button) {
            return;
        }

        const panel = button.closest('[data-comment-vote-panel]');

        if (!panel || panel.dataset.loading === 'true') {
            return;
        }

        panel.dataset.loading = 'true';

        try {
            const response = await fetch(button.dataset.voteUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({
                    vote: Number(button.dataset.voteValue),
                }),
            });

            if (response.status === 401 || response.status === 419) {
                window.location.href = button.dataset.loginUrl;
                return;
            }

            if (!response.ok) {
                return;
            }

            updateVotePanel(panel, await response.json());
        } finally {
            panel.dataset.loading = 'false';
        }
    });
}

function updateVotePanel(panel, data) {
    panel.querySelector('[data-vote-count="likes"]').textContent = data.likes;
    panel.querySelector('[data-vote-count="dislikes"]').textContent = data.dislikes;

    panel.querySelectorAll('[data-vote-button]').forEach((button) => {
        const isActive = Number(button.dataset.voteValue) === Number(data.user_vote);
        const isLikeButton = button.dataset.voteValue === '1';

        button.classList.toggle('bg-red-600', isActive && isLikeButton);
        button.classList.toggle('bg-red-600', isActive && !isLikeButton);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('text-slate-500', !isActive);
    });
}
