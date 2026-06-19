document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy]');
    if (!button) {
        return;
    }

    const input = document.querySelector(button.dataset.copy);
    if (!input) {
        return;
    }

    input.select();
    input.setSelectionRange(0, input.value.length);

    try {
        await navigator.clipboard.writeText(input.value);
        const originalText = button.textContent;
        button.textContent = 'Copie';
        window.setTimeout(() => {
            button.textContent = originalText;
        }, 1400);
    } catch {
        document.execCommand('copy');
    }
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm || 'Confirmer cette action ?')) {
            event.preventDefault();
        }
    });
});

const memoryBoard = document.querySelector('#memory-board');
if (memoryBoard) {
    const symbols = ['A', 'B', 'C', 'D', 'E', 'F'];
    const resetButton = document.querySelector('#memory-reset');
    const movesEl = document.querySelector('#memory-moves');
    const pairsEl = document.querySelector('#memory-pairs');
    const resultEl = document.querySelector('#memory-result');
    let firstCard = null;
    let lock = false;
    let moves = 0;
    let pairs = 0;

    const shuffle = (items) => {
        const copy = [...items];
        for (let index = copy.length - 1; index > 0; index -= 1) {
            const swapIndex = Math.floor(Math.random() * (index + 1));
            [copy[index], copy[swapIndex]] = [copy[swapIndex], copy[index]];
        }
        return copy;
    };

    const render = () => {
        firstCard = null;
        lock = false;
        moves = 0;
        pairs = 0;
        movesEl.textContent = '0';
        pairsEl.textContent = '0/6';
        resultEl.hidden = true;
        memoryBoard.innerHTML = '';

        shuffle([...symbols, ...symbols]).forEach((symbol, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'memory-card';
            button.dataset.symbol = symbol;
            button.dataset.index = String(index);
            button.textContent = '';
            button.setAttribute('aria-label', 'Carte cachee');
            memoryBoard.appendChild(button);
        });
    };

    memoryBoard.addEventListener('click', (event) => {
        const card = event.target.closest('.memory-card');
        if (!card || lock || card.classList.contains('is-found') || card === firstCard) {
            return;
        }

        card.textContent = card.dataset.symbol;
        card.classList.add('is-open');

        if (!firstCard) {
            firstCard = card;
            return;
        }

        moves += 1;
        movesEl.textContent = String(moves);

        if (firstCard.dataset.symbol === card.dataset.symbol) {
            firstCard.classList.add('is-found');
            card.classList.add('is-found');
            firstCard = null;
            pairs += 1;
            pairsEl.textContent = `${pairs}/6`;

            if (pairs === 6) {
                resultEl.hidden = false;
                resultEl.textContent = `Partie terminee en ${moves} coups`;
            }
            return;
        }

        lock = true;
        window.setTimeout(() => {
            firstCard.textContent = '';
            card.textContent = '';
            firstCard.classList.remove('is-open');
            card.classList.remove('is-open');
            firstCard = null;
            lock = false;
        }, 760);
    });

    resetButton.addEventListener('click', render);
    render();
}
