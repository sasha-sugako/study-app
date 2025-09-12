import ModalComponent from "./modal.js";
import removeDeckModal from "./removeDeckModal.js";

// Extends ModalComponent to create modal for creating and editing a deck
export default class editDeckModal extends ModalComponent {
    #action = null;
    constructor(openBtn, action) {
        super(openBtn, 'dialog', 'closeModal')
        this.#action = action;
    }

    // Displays the modal and handles deck creation or editing
    async showModal() {
        // Set modal content (form structure)
        this.modal.innerHTML = `<button id="closeModal">❌</button>
        <h2 id="modalTitle">Nová kolekce</h2>
        <form id="modalForm">
            <label for="name">Nazev</label>
            <input type="text" id="name" name="name" required>
    
            <label for="about">Popis</label>
            <textarea id="about" name="about"></textarea>

            <div id="categories">
                <p>Kategorie</p>
                <div id="categories_container"></div>
                <label for="new_category">Přidat novou kategorii</label>
                <input type="text" id="new_category" name="new_category">
            </div>
    
            <button id="submitBtn" type="submit">Vytvořit</button>
        </form>
        `;
        const title = document.getElementById("modalTitle");
        const nameInput = document.getElementById("name");
        const aboutInput = document.getElementById("about");
        const newCategoryInput = document.getElementById("new_category");
        const targetURL = this.openBtn.dataset.url;
        const submitBtn = document.getElementById('submitBtn');
        // Load categories from the API
        try{
            const res = await fetch('/api/categories', {
                method: "GET",
                headers: { "Accept": "application/json" }
            });
            const data = await res.json();
            const container = document.getElementById("categories_container");
            // Display available categories as checkboxes
            data.data.forEach(category => {
                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.value = category.name;
                checkbox.checked = false;
                checkbox.id = 'checkbox_'+category.id;
                checkbox.classList.add('custom_checkbox');
                const label = document.createElement("label");
                label.textContent = category.name;
                label.setAttribute("for", 'checkbox_'+category.id);
                container.appendChild(checkbox);
                container.appendChild(label);
            });
        }
        catch(err) {
            alert(err);
            return;
        }
        // If editing an existing deck, fetch and pre-fill data
        if (this.#action === 'edit_collection'){
            title.textContent = 'Upravit kolekci';
            let url = targetURL.replace('my_decks', 'deck');
            try{
                const res = await fetch(url, {
                    method: "GET",
                    headers: { "Accept": "application/json" }
                });
                const data = await res.json();
                nameInput.value = data.name;
                if (data.description)
                    aboutInput.value = data.description;
                // Check previously selected categories
                data.categories.forEach(category => {
                    const checkbox = document.getElementById('checkbox_'+category.id);
                    checkbox.checked = true;
                });
                submitBtn.textContent = 'Upravit';
            }
            catch(err){
                alert(err);
                return;
            }
        }
        this.modal.showModal();
        // Handle form submission
        submitBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            submitBtn.disabled = true;
            if (nameInput.value === ""){
                alert('Zadejte název');
                return;
            }
            // If new category entered, create it
            if (newCategoryInput.value !== ""){
                const new_category = {
                    name: newCategoryInput.value
                }
                try{
                    const res = await fetch('/api/categories', {
                        method: 'POST',
                        headers: {
                            "Accept": "application/json",
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify(new_category)
                    })
                    if (!res.ok){
                        alert('Chyba vytvoření nové kategorie');
                        return;
                    }
                }
                catch(err){
                    alert(err);
                    return;
                }
            }
            const dataInput = {
                name: nameInput.value,
                description: aboutInput.value,
                categories: [...document.querySelectorAll(".custom_checkbox:checked")].map(checkbox => checkbox.value),
            };
            if (newCategoryInput.value !== "")
                dataInput.categories.push(newCategoryInput.value);
            const method = this.#action === 'edit_collection' ? 'PUT' : 'POST';
            // Sends POST or PUT request to create or edit deck
            try{
                const res = await fetch(targetURL, {
                    method: method,
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(dataInput)
                });
                if(!res.ok){
                    alert('Chyba vytvoření kolekci');
                    return;
                }
                const data = await res.json();
                // If creating a new collection, update the DOM to show it
                if (this.#action === 'create_collection'){
                    const new_element = document.createElement("div");
                    new_element.classList.add('deck');
                    new_element.innerHTML = `
                        <h3>${data.name}</h3>
                        <div class="deck_info">
                            <p>Karticek v kolekci: ${data.cards.length}</p>
                            <div class="progress_container">
                                <div class="progress_bar" style="width:0;"></div>
                            </div>
                            <div class="deck_edit">
                                <a id="openModal"
                                   data-value="edit_collection"
                                   data-url="/api/decks/my_decks/${data.id}"
                                   href="/decks/my_decks/${data.id}/edit"><i class="fa-solid fa-pen"></i></a>
                                <a id="openModal"
                                   data-value="remove_collection"
                                   data-collection="${data.name}"
                                   data-url="/api/decks/my_decks/${data.id}"
                                   href="/decks/my_decks/${data.id}/remove"><i class="fa-solid fa-trash"></i></a>
                                <a href="/decks/deck/${data.id}"><i class="fa-solid fa-play"></i></a>
                            </div>
                        </div>`;
                    const decks = document.querySelector('.decks');
                    if (decks)
                        decks.appendChild(new_element);
                    else {
                        const parent = document.querySelector('.my_decks');
                        parent.removeChild(parent.lastElementChild);
                        const decks_div = document.createElement("div");
                        decks_div.classList.add('decks');
                        parent.appendChild(decks_div);
                        decks_div.appendChild(new_element);
                    }
                    // Attach modals to new buttons
                    new_element.querySelectorAll('#openModal').forEach((element) => {
                        let action = element.dataset.value;
                        if (action === 'remove_collection')
                            new removeDeckModal(element);
                        if (action === "edit_collection")
                            new editDeckModal(element, action);
                    })
                }
                // If editing, update the title of the deck in DOM
                else{
                    const deck = this.openBtn.parentElement.parentElement.parentElement;
                    const title = deck.querySelector('h3');
                    title.textContent = data.name;
                }
            }
            catch(err){
                alert(err);
                return;
            }
            this.modal.close();
            submitBtn.disabled = false;
        });
    }
}