import ModalComponent from "./modal.js";

// Extends ModalComponent to create a confirmation modal for deleting a deck
export default class removeDeckModal extends ModalComponent{
    constructor(openBtn){
        super(openBtn, 'dialog', 'closeModal');
    }

    // Displays a confirmation dialog and handles deck deletion
    showModal() {
        const collection = this.openBtn.getAttribute('data-collection');
        // Injects confirmation message and button into modal
        this.modal.innerHTML = `<button id="closeModal">❌</button>
            <div class="modal_info">
               <p>Skutečně si přejete odstranit kolekci <strong>${collection}</strong>?</p>
               <button id="submitBtn">Podtvrdit</button>
            </div>
            `;
        this.modal.showModal();
        const submitBtn = document.getElementById('submitBtn');
        const deck = this.openBtn.parentElement.parentElement.parentElement;
        const targetURL = this.openBtn.dataset.url;
        // Sends DELETE request to remove the deck and updates the UI
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
                    // Removes the deck element from the DOM and redirects the user
                    deck.remove();
                    this.modal.close();
                    window.location.href = `/decks/my_decks`;
                }).catch(err => alert(err));
            }
        });
    }
}