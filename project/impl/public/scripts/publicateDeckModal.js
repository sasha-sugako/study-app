import ModalComponent from "./modal.js";

// Extends ModalComponent to create a confirmation modal for publishing a deck
export default class publicateDeckModal extends ModalComponent{
    constructor(openBtn){
        super(openBtn, 'dialog', 'closeModal');
    }

    // Displays the modal and handles deck publishing
    showModal() {
        const collection = this.openBtn.getAttribute('data-collection');
        // Inserts confirmation message and submit button into the modal
        this.modal.innerHTML = `<button id="closeModal">❌</button>
            <div class="modal_info">
               <p>Skutečně si přejete publikovat kolekci <strong>${collection}</strong>?</p>
               <button id="submitBtn">Podtvrdit</button>
            </div>
            `;
        this.modal.showModal();
        const submitBtn = document.getElementById('submitBtn');
        const targetURL = this.openBtn.dataset.url;
        // Sends POST request to publish the deck and redirects the user
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
            window.location.href = `/decks`;
        });
    }
}