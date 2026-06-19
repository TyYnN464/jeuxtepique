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

const connect4Board = document.querySelector('#connect4-board');
if (connect4Board) {
    const columnsEl = document.querySelector('#connect4-columns');
    const resetButton = document.querySelector('#connect4-reset');
    const turnEl = document.querySelector('#connect4-turn');
    const movesEl = document.querySelector('#connect4-moves');
    const resultEl = document.querySelector('#connect4-result');
    const rows = 6;
    const columns = 7;
    let board = [];
    let moves = 0;
    let finished = false;

    const newBoard = () => Array.from({ length: rows }, () => Array(columns).fill(''));

    const renderConnect4 = () => {
        connect4Board.innerHTML = '';
        columnsEl.innerHTML = '';

        for (let column = 0; column < columns; column += 1) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'connect4-column-button';
            button.dataset.column = String(column);
            button.textContent = `Colonne ${column + 1}`;
            button.disabled = finished || board[0][column] !== '';
            columnsEl.appendChild(button);
        }

        for (let row = 0; row < rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
                const cell = document.createElement('div');
                cell.className = 'connect4-cell';
                if (board[row][column]) {
                    cell.classList.add(board[row][column] === 'player' ? 'is-player' : 'is-bot');
                }
                connect4Board.appendChild(cell);
            }
        }

        movesEl.textContent = String(moves);
    };

    const resetConnect4 = () => {
        board = newBoard();
        moves = 0;
        finished = false;
        turnEl.textContent = 'Joueur';
        resultEl.hidden = true;
        renderConnect4();
    };

    const dropToken = (column, owner) => {
        for (let row = rows - 1; row >= 0; row -= 1) {
            if (board[row][column] === '') {
                board[row][column] = owner;
                moves += 1;
                return row;
            }
        }
        return -1;
    };

    const availableColumns = () => {
        const available = [];
        for (let column = 0; column < columns; column += 1) {
            if (board[0][column] === '') {
                available.push(column);
            }
        }
        return available;
    };

    const hasFour = (owner) => {
        const directions = [
            [0, 1],
            [1, 0],
            [1, 1],
            [1, -1],
        ];

        for (let row = 0; row < rows; row += 1) {
            for (let column = 0; column < columns; column += 1) {
                if (board[row][column] !== owner) {
                    continue;
                }

                for (const [rowStep, columnStep] of directions) {
                    let count = 1;
                    for (let step = 1; step < 4; step += 1) {
                        const nextRow = row + rowStep * step;
                        const nextColumn = column + columnStep * step;
                        if (
                            nextRow < 0 ||
                            nextRow >= rows ||
                            nextColumn < 0 ||
                            nextColumn >= columns ||
                            board[nextRow][nextColumn] !== owner
                        ) {
                            break;
                        }
                        count += 1;
                    }
                    if (count === 4) {
                        return true;
                    }
                }
            }
        }

        return false;
    };

    const finishIfNeeded = (owner) => {
        if (hasFour(owner)) {
            finished = true;
            turnEl.textContent = 'Termine';
            resultEl.hidden = false;
            resultEl.textContent = owner === 'player' ? 'Victoire joueur' : 'Victoire machine';
            renderConnect4();
            return true;
        }

        if (availableColumns().length === 0) {
            finished = true;
            turnEl.textContent = 'Termine';
            resultEl.hidden = false;
            resultEl.textContent = 'Egalite';
            renderConnect4();
            return true;
        }

        return false;
    };

    const botColumn = () => {
        const available = availableColumns();

        for (const owner of ['bot', 'player']) {
            for (const column of available) {
                const trial = newBoard();
                for (let row = 0; row < rows; row += 1) {
                    trial[row] = [...board[row]];
                }
                for (let row = rows - 1; row >= 0; row -= 1) {
                    if (trial[row][column] === '') {
                        trial[row][column] = owner;
                        break;
                    }
                }
                const saved = board;
                board = trial;
                const wins = hasFour(owner);
                board = saved;
                if (wins) {
                    return column;
                }
            }
        }

        const preference = [3, 2, 4, 1, 5, 0, 6].filter((column) => available.includes(column));
        return preference[Math.floor(Math.random() * Math.min(preference.length, 3))] ?? available[0];
    };

    columnsEl.addEventListener('click', (event) => {
        const button = event.target.closest('.connect4-column-button');
        if (!button || finished) {
            return;
        }

        const column = Number.parseInt(button.dataset.column, 10);
        if (!Number.isInteger(column) || dropToken(column, 'player') === -1) {
            return;
        }

        if (finishIfNeeded('player')) {
            return;
        }

        turnEl.textContent = 'Machine';
        renderConnect4();

        window.setTimeout(() => {
            const botMove = botColumn();
            if (botMove !== undefined) {
                dropToken(botMove, 'bot');
            }
            if (!finishIfNeeded('bot')) {
                turnEl.textContent = 'Joueur';
                renderConnect4();
            }
        }, 380);
    });

    resetButton.addEventListener('click', resetConnect4);
    resetConnect4();
}
