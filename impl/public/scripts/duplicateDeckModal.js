import ModalComponent from "./modal.js";

// Extends ModalComponent to create a confirmation modal for duplicating a deck
export default class duplicateDeckModal extends ModalComponent{
    constructor(openBtn){
        super(openBtn, 'dialog', 'closeModal')
    }

    // Displays a confirmation dialog and handles deck duplication
    showModal() {
        const collection = this.openBtn.getAttribute('data-collection');
        // Injects confirmation message and button into modal
        this.modal.innerHTML = `<button id="closeModal">❌</button>
            <div class="modal_info">
                <p>Skutečně si přejete duplikovat kolekci <strong>${collection}</strong>?</p>
                <button id="submitBtn">Podtvrdit</button>
            </div>
            `;
        this.modal.showModal();
        const submitBtn = document.getElementById('submitBtn');
        const targetURL = this.openBtn.dataset.url;
        // Sends POST request to duplicate the deck and updates the UI
        submitBtn.addEventListener("click", event => {
            event.preventDefault();
            if (targetURL) {
                fetch(targetURL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                }).then(res => {
                    if (res.status < 200 || res.status >= 300) {
                        throw new Error();
                    }
                }).catch(err => alert(err));
            }
            this.modal.close();
            window.location.href = `/decks/my_decks`;
        });
    }
}