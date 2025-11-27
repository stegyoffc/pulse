// js/merch.js

document.addEventListener('DOMContentLoaded', () => {
    // --- ELEMENTY ---
    const overlay        = document.getElementById('merchModalOverlay');
    const modalClose     = document.getElementById('merchModalClose');
    const modalImage     = document.getElementById('merchModalImage');
    const modalTitle     = document.getElementById('merchModalTitle');
    const modalDesc      = document.getElementById('merchModalDescription');
    const colorDots      = Array.from(document.querySelectorAll('.color-dot'));
    const sizeBtns       = Array.from(document.querySelectorAll('.size-btn'));
    const qtyMinus       = document.getElementById('qtyMinus');
    const qtyPlus        = document.getElementById('qtyPlus');
    const qtyDisplay     = document.getElementById('qtyDisplay');
    const addToCartBtn   = document.getElementById('modalAddToCart');
    const goToFormBtn    = document.getElementById('goToFormBtn');
    const cartInput      = document.getElementById('cart_json');
    const colorPickerBox = document.getElementById('colorPicker');
    const sizeBox        = document.getElementById('sizeOptions');
    const qtyBox         = document.getElementById('qtyBox');
  
    const merchButtons   = Array.from(document.querySelectorAll('.merch-open'));
  
    // --- STAV ---
    let cart = []; // {id,title,price,size,color,qty}
    let currentProduct = null;
    let currentSize = null;
    let currentColor = 'white';
    let currentQty = 1;
  
    // --- POMOCNÉ FUNKCE ---
  
    function setColor(color) {
      currentColor = color;
      colorDots.forEach(dot => {
        dot.classList.toggle('active', dot.dataset.color === color);
      });
    }
  
    function setSize(size) {
      currentSize = size;
      sizeBtns.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.size === size);
      });
    }
  
    function setQty(q) {
      currentQty = Math.max(1, q);
      qtyDisplay.textContent = currentQty;
    }
  
    function openModal(button) {
      const id    = button.dataset.product;
      const title = button.dataset.title || '';
      const desc  = button.dataset.description || '';
      const img   = button.dataset.image || '';
      const price = parseInt(button.dataset.price || '0', 10) || 0;
  
      currentProduct = { id, title, price };
  
      modalTitle.textContent = title;
      modalDesc.textContent  = desc;
  
      if (img) {
        modalImage.src = img;
        modalImage.alt = title;
      } else {
        modalImage.removeAttribute('src');
        modalImage.alt = 'Merch PULSE';
      }
  
      // Výchozí stav
      setColor('white');
      setSize(null);
      setQty(1);
  
      // Bracelet – bez barvy a velikosti, jen množství
      if (id === 'bracelet') {
        colorPickerBox.style.display = 'none';
        sizeBox.style.display        = 'none';
        qtyBox.style.display         = 'flex';
      } else {
        colorPickerBox.style.display = 'flex';
        sizeBox.style.display        = 'flex';
        qtyBox.style.display         = 'flex';
      }
  
      overlay.classList.add('active');
    }
  
    function closeModal() {
      overlay.classList.remove('active');
      currentProduct = null;
      currentSize = null;
      currentQty = 1;
    }
  
    function updateCartInput() {
      cartInput.value = JSON.stringify(cart);
      const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
      if (totalItems > 0) {
        goToFormBtn.disabled = false;
      } else {
        goToFormBtn.disabled = true;
      }
    }
  
    function updateProductSummaries() {
      const byProduct = {};
      cart.forEach(item => {
        if (!byProduct[item.id]) byProduct[item.id] = { qty: 0 };
        byProduct[item.id].qty += item.qty;
      });
  
      document.querySelectorAll('.merch-card').forEach(card => {
        const id = card.querySelector('.merch-open')?.dataset.product;
        const summaryEl = card.querySelector('.merch-summary');
        if (!id || !summaryEl) return;
  
        const info = byProduct[id];
        if (!info) {
          summaryEl.textContent = 'Zatím nic vybráno.';
        } else {
          summaryEl.textContent = `V košíku: ${info.qty} ks`;
        }
      });
    }
  
    // --- LISTENERY ---
  
    // Otevření modalu
    merchButtons.forEach(btn => {
      btn.addEventListener('click', () => openModal(btn));
    });
  
    // Zavření modalu
    modalClose.addEventListener('click', closeModal);
    overlay.addEventListener('click', e => {
      if (e.target === overlay) closeModal();
    });
  
    // Barvy
    colorDots.forEach(dot => {
      dot.addEventListener('click', () => {
        setColor(dot.dataset.color);
      });
    });
  
    // Velikosti
    sizeBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        setSize(btn.dataset.size);
      });
    });
  
    // Množství
    qtyMinus.addEventListener('click', () => {
      setQty(currentQty - 1);
    });
  
    qtyPlus.addEventListener('click', () => {
      setQty(currentQty + 1);
    });
  
    // Přidat do košíku
    addToCartBtn.addEventListener('click', () => {
      if (!currentProduct) return;
  
      let size = currentSize;
      let color = currentColor;
  
      // Bracelet – nemá velikost a barvu neřešíme
      if (currentProduct.id === 'bracelet') {
        size = 'UNI';
        color = null;
      } else {
        if (!size) {
          alert('Vyber prosím velikost.');
          return;
        }
      }
  
      const item = {
        id:    currentProduct.id,
        title: currentProduct.title,
        price: currentProduct.price,
        size,
        color,
        qty:   currentQty
      };
  
      cart.push(item);
      updateCartInput();
      updateProductSummaries();
      closeModal();
    });
  
    // inicializace
    updateCartInput();
    updateProductSummaries();
  });