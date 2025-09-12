import ModalComponent from "./modal.js";

// Extends ModalComponent to create a modal for duplicating a card
export default class duplicateCardModal extends ModalComponent{
    #parent_modal = null;
    #parentBtn = null;
    constructor(openBtn, parentBtn) {
        super(openBtn, 'card_dialog', 'closeCardModal')
        this.#parentBtn = parentBtn;
        this.#parent_modal = document.getElementById("dialog");
    }

    // Displays a dialog and handles card duplication
    async showModal() {
        // Modal form HTML structure
        this.modal.innerHTML = `<button id="closeCardModal">❌</button>
            <div class="modal_info">
                <p>Zvolte kolekci</p>
                <select name="deck" id="dropdown"></select>
                <button id="submitBtn">Zkopírovat</button>
            </div>
            `;
        // Fetch decks where card can be duplicated
        try{
            const res = await fetch('/api/decks', {
                method: 'GET',
                headers: {
                    "Accept": "application/json"
                }});
            const data = await res.json();
            const select = document.getElementById("dropdown");
            data.data.forEach(deck => {
                const option = document.createElement("option");
                option.textContent = deck.name;
                option.value = deck.id;
                select.appendChild(option);
            })
        }
        catch(err){
            alert(err);
            return
        }
        this.modal.showModal();
        const submitBtn = document.getElementById('submitBtn');
        const targetURL = this.openBtn.dataset.url;
        // Sends POST request to duplicate the card and updates the UI
        submitBtn.addEventListener("click", event => {
            event.preventDefault();
            const new_deck = document.getElementById("dropdown").value
            const data = {
                id: Number(new_deck)
            }
            if (targetURL) {
                fetch(targetURL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                }).then(res => {
                    if (res.status < 200 || res.status >= 300) {
                        throw new Error();
                    }
                }).catch(err => alert(err));
            }
            this.modal.close();
            this.#parent_modal.close()
            window.location.href = `/decks/deck/`+new_deck;
        });
    }
}