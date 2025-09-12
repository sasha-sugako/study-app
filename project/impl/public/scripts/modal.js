// Represents a modal dialog component with open and close functionality
export default class ModalComponent{
    openBtn = null;
    modal = null;
    closeBtn = null

    constructor(openBtn, dialog, closeBtn){
        this.openBtn = openBtn;
        this.modal = document.getElementById(dialog);
        this.closeBtn = closeBtn
        this.#createModal();
    }

    // Sets up event listeners to open and close the modal
    #createModal(){
        this.openBtn.addEventListener('click', event => {
            event.preventDefault();
            this.showModal();
            const closeBtn = document.getElementById(this.closeBtn);
            closeBtn.addEventListener("click", () => this.modal.close());
        });
    }

    // Displays the modal
    showModal(){}
}