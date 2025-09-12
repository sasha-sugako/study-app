import ModalComponent from "./modal.js";

// Extends ModalComponent to create a confirmation modal for deleting a card
export default class removeCardModal extends ModalComponent{
    #parent_modal = null;
    #parentBtn = null;
    constructor(openBtn, parentBtn) {
        super(openBtn, 'card_dialog', 'closeCardModal')
        this.#parentBtn = parentBtn;
        this.#parent_modal = document.getElementById("dialog");
    }

    // Displays a confirmation dialog and handles card deletion
    showModal() {
        // Injects confirmation message and button into modal
        this.modal.innerHTML = `<button id="closeCardModal">❌</button>
            <div class="modal_info">
               <p>Skutečně si přejete odstranit kartičku?</p>
               <button id="submitBtn">Podtvrdit</button>
            </div>
            `;
        this.modal.showModal();
        const submitBtn = document.getElementById('submitBtn');
        const card = this.#parentBtn.parentElement;
        const targetURL = this.openBtn.dataset.url;
        // Sends DELETE request to remove the card and updates the UI
        submitBtn.addEventListener("click", event => {
            event.preventDefault();
            if (targetURL) {
                fetch(targetURL, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                    },
                }).then(res => {
                    if (res.status < 200 || res.status >= 300) {
                        throw new Error();
                    }
                    this.#parent_modal.close()
                    // Removes the card element from the DOM
                    card.remove();
                }).catch(err => alert(err));
            }
            // Decrease count of cards in the deck
            const cards_head = document.querySelector('.cards_head p');
            if (cards_head){
                const text = cards_head.textContent;
                const match = text.match(/\d+/);
                if (match) {
                    const currentCount = parseInt(match[0]);
                    const newCount = currentCount - 1;
                    cards_head.textContent = text.replace(`${currentCount}`, `${newCount}`);
                }
            }
            this.modal.close();
        });
    }
}