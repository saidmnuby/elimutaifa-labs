<script>
    (() => {
        document.querySelectorAll('[data-github-live-check]').forEach((input) => {
            const status = document.getElementById(input.dataset.statusTarget);
            if (!status) return;

            const checkType = input.dataset.githubLiveCheck;
            let timer = null;
            let controller = null;

            const show = (state, message) => {
                status.hidden = false;
                status.dataset.state = state;
                status.textContent = message;
            };

            input.addEventListener('input', () => {
                const value = input.value.trim();
                clearTimeout(timer);
                controller?.abort();

                if (!value) {
                    status.hidden = true;
                    status.textContent = '';
                    return;
                }

                const isUsername = checkType === 'username';
                const valid = isUsername
                    ? /^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/.test(value)
                    : /^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/.test(value);

                if (!valid) {
                    show('unknown', isUsername
                        ? 'Enter a valid GitHub username.'
                        : 'Enter the repository as owner/repository.');
                    return;
                }

                show('checking', 'Checking GitHub...');
                timer = setTimeout(async () => {
                    controller = new AbortController();
                    const endpoint = new URL(input.dataset.githubCheckUrl, window.location.origin);
                    endpoint.searchParams.set(checkType, value);

                    try {
                        const response = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json' },
                            signal: controller.signal,
                        });

                        if (response.status === 429) {
                            show('unknown', 'Too many checks. Wait a moment and try again.');
                            return;
                        }

                        if (!response.ok) {
                            show('unknown', 'GitHub could not verify this now. Try again shortly.');
                            return;
                        }

                        const result = await response.json();
                        if (input.value.trim() !== value) return;

                        if (result.status === 'found') {
                            show('found', isUsername
                                ? 'GitHub profile exists. This does not verify account ownership.'
                                : `GitHub App access verified${result.private ? ' (private repository)' : ''}.`);
                        } else if (result.status === 'not_found') {
                            show('not-found', 'GitHub username was not found. Check the spelling.');
                        } else if (result.status === 'not_publicly_visible') {
                            show('unknown', 'Not visible publicly. It may be private or misspelled; admin will review it.');
                        } else if (result.status === 'installation_required') {
                            show('unknown', 'Connect the GitHub App and select this repository first.');
                        } else if (result.status === 'not_accessible') {
                            show('not-found', 'The GitHub App does not have access to this repository. Select it during installation.');
                        } else {
                            show('unknown', 'GitHub could not verify this now. Try again shortly.');
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            show('unknown', 'GitHub could not verify this now. Try again shortly.');
                        }
                    }
                }, 450);
            });

            if (input.value.trim()) input.dispatchEvent(new Event('input'));
        });
    })();
</script>
