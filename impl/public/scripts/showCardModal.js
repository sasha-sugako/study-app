import ModalComponent from "./modal.js";
import duplicateCardModal from "./duplicateCardModal.js";
import removeCardModal from "./removeCardModal.js";
import editCardModal from "./editCardModal.js";
import createCardModal from "./createCardModal.js";

// Extends the ModalComponent to create a custom modal to view
export default class showCardModal extends ModalComponent {
    constructor(openBtn) {
        super(openBtn, 'dialog', 'closeModal');
    }

    // Asynchronously loads and displays card data inside a modal dialog
    async showModal() {
        const targetURL = this.openBtn.dataset.url;
        // Injects initial modal HTML structure with placeholders
        this.modal.innerHTML = `<button id="closeModal">❌</button>
            <div class="modal_card">
                <div class="front">
                    <p id="front_side"></p>
                </div>
               <div class="back">
                    <p id="back_side"></p>
               </div>
            </div>
            <div class="modal_card_edit">
               <a 
                  id="openCardModal"
                  data-value="card_copy"
                  href="#"
                  title="Klonovat"><i class="fa-solid fa-share-square"></i></a>
            </div>`;
        try{
            // Fetches card data from the server as JSON
            const res = await fetch(targetURL,
                { method: "GET",
                    headers: { "Accept": "application/json" }
                });
            const data = await res.json();

            // Fills in card front and back text content
            document.getElementById('front_side').textContent = data.front_side;
            document.getElementById('back_side').textContent = data.back_side;
            // Sets the data-url for the clone button
            this.modal.querySelector('#openCardModal').setAttribute("data-url", data._self);
            // Adds images to the card if present
            if (data.front_image){
                const img = document.createElement('img');
                img.src = `/uploads/img/${data.front_image}`;
                document.getElementById('front_side').before(img);
            }
            if (data.back_image){
                const img = document.createElement('img');
                img.src = `/uploads/img/${data.back_image}`;
                document.getElementById('back_side').before(img);
            }
            // If control actions are allowed, add edit and delete buttons
            if (this.openBtn.dataset.control){
                const editBtn = document.createElement('a');
                editBtn.id = 'openCardModal';
                editBtn.setAttribute("data-value", "card_edit");
                editBtn.setAttribute("data-url", data._self);
                editBtn.href = "#";
                editBtn.title = "Upravit";
                editBtn.innerHTML = '<i class="fa-solid fa-pen"></i>';

                const removeBtn = document.createElement('a');
                removeBtn.id = 'openCardModal';
                removeBtn.setAttribute("data-value", "card_remove");
                removeBtn.setAttribute("data-url", data._self);
                removeBtn.href = "#";
                removeBtn.title = "Odstranit";
                removeBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';

                const editBtns = this.modal.querySelector('.modal_card_edit');
                editBtns.appendChild(editBtn);
                editBtns.appendChild(removeBtn);
            }
        }
        catch(err){
            alert(err); // Shows an alert if the request fails
            return;
        }
        // Displays the modal dialog
        this.modal.showModal();
        // Initializes modal actions based on each button’s data-value
        document.querySelectorAll('#openCardModal').forEach(
            (el) => {
                let action = el.dataset.value;
                if (action === "card_copy")
                    new duplicateCardModal(el, this.openBtn);
                if (action === 'card_remove')
                    new removeCardModal(el, this.openBtn);
                if (action === "card_edit")
                    new editCardModal(el, this.openBtn);
                if (action === 'card_create')
                    new createCardModal(el);
            });
    }
}