import ModalComponent from "./modal.js";

// Extends the ModalComponent to create a custom modal for submitting a review
export default class reviewDeckModal extends ModalComponent {
    constructor(openBtn) {
        super(openBtn, 'dialog', 'closeModal');
    }

    // Displays a modal form allowing the user to submit a star rating and description
    showModal() {
        // Injects the HTML structure of the review form into the modal
        this.modal.innerHTML = `<button id="closeModal">❌</button>
        <h2 id="modalTitle">Zanechat recenzi</h2>
        <form id="modalForm">
            <label for="rate">Hodnocení kolekce</label>
            <div class="rating">
                <label>
                    <input type="radio" id="rate_0" name="rate[rate]" required="required" value="5">
                    <i class="fa-solid fa-star"></i>
                </label>
                <label>
                    <input type="radio" id="rate_1" name="rate[rate]" required="required" value="4">
                    <i class="fa-solid fa-star"></i>
                </label>
                <label>
                    <input type="radio" id="rate_2" name="rate[rate]" required="required" value="3">
                    <i class="fa-solid fa-star"></i>
                </label>
                <label>
                    <input type="radio" id="rate_3" name="rate[rate]" required="required" value="2">
                    <i class="fa-solid fa-star"></i>
                </label>
                <label>
                    <input type="radio" id="rate_4" name="rate[rate]" required="required" value="1">
                    <i class="fa-solid fa-star"></i>
                </label>
            </div>
    
            <label for="rate_description">Popis</label>
            <textarea maxlength="255" id="rate_description" name="rate_description"></textarea>
    
            <button id="submitBtn" type="submit">Potvrdit</button>
        </form>
        `;
        // Handles star rating UI logic and coloring
        const ratingInput = document.querySelector(".rating");
        const stars = ratingInput.querySelectorAll('label');
        let value = null;
        stars.forEach(label => {
            label.addEventListener('click', () => {
                value = parseInt(label.querySelector('input').value);
                stars.forEach(s => {
                    const starValue = parseInt(s.querySelector('input').value);
                    const icon = s.querySelector('i');
                    if (starValue <= value)
                        icon.style.color = '#9ee195';
                    else
                        icon.style.color = '#AAAAAAC1';
                })
            })
        });
        const descriptionInput = document.getElementById("rate_description");
        const targetURL = this.openBtn.dataset.url;
        const submitBtn = document.getElementById('submitBtn');
        this.modal.showModal();
        // Sends form data as JSON via POST request on submit
        submitBtn.addEventListener('click', event => {
            event.preventDefault();
            if (! value){
                alert('Zadejte hodnocení');
                return;
            }
            const data = {
                rate: value,
                description: descriptionInput.value
            };
            fetch(targetURL, {
                method: 'POST',
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data)
            }).then(res => {
                if (res.status < 200 || res.status >= 300) {
                    throw new Error()
                }
                return res.json();
            }).catch(err => alert(err));
            // Closes the modal after submission
            this.modal.close();
        });
    }
}