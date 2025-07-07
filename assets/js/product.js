// Enhanced Product Page JavaScript for Yarac Fashion Store

// Global variables for product page
let searchTimeout
let currentSearchQuery = ""
let isSearching = false

// Initialize product page functionality
document.addEventListener("DOMContentLoaded", () => {
  initializeProductSearch()
  initializeProductFilters()
  initializeProductAnimations()
  initializeProductInteractions()
  initializeAdvancedFilters()
})

// Enhanced search functionality
function initializeProductSearch() {
  const searchInput = document.getElementById("search-input")
  const searchSuggestions = document.getElementById("search-suggestions")
  const searchStats = document.getElementById("search-stats")

  if (!searchInput) return

  // Real-time search with debouncing
  searchInput.addEventListener(
    "input",
    debounce(function () {
      const query = this.value.trim()
      currentSearchQuery = query

      if (query.length >= 2) {
        performLiveSearch(query)
        fetchSearchSuggestions(query)
      } else {
        clearSearchResults()
        hideSuggestions()
      }
    }, 300),
  )

  // Handle Enter key
  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault()
      performFullSearch()
    }
  })

  // Handle search suggestions selection
  searchInput.addEventListener("keydown", (e) => {
    const suggestions = document.querySelectorAll(".suggestion-item")
    let currentIndex = -1

    // Find currently selected suggestion
    suggestions.forEach((item, index) => {
      if (item.classList.contains("selected")) {
        currentIndex = index
      }
    })

    if (e.key === "ArrowDown") {
      e.preventDefault()
      currentIndex = Math.min(currentIndex + 1, suggestions.length - 1)
      updateSelectedSuggestion(suggestions, currentIndex)
    } else if (e.key === "ArrowUp") {
      e.preventDefault()
      currentIndex = Math.max(currentIndex - 1, -1)
      updateSelectedSuggestion(suggestions, currentIndex)
    } else if (e.key === "Enter" && currentIndex >= 0) {
      e.preventDefault()
      suggestions[currentIndex].click()
    }
  })
}

// Perform live search (filter visible products)
function performLiveSearch(query) {
  const productCards = document.querySelectorAll(".product-card")
  const searchStats = document.getElementById("search-stats")
  let matchCount = 0

  isSearching = true

  productCards.forEach((card) => {
    const productName = card.getAttribute("data-name") || ""
    const productCategory = card.getAttribute("data-category") || ""

    const isMatch = productName.includes(query.toLowerCase()) || productCategory.includes(query.toLowerCase())

    if (isMatch) {
      card.style.display = "block"
      card.classList.add("search-match")
      card.classList.remove("search-hidden")
      matchCount++
    } else {
      card.classList.add("search-hidden")
      card.classList.remove("search-match")
    }
  })

  // Update search stats
  if (searchStats) {
    searchStats.innerHTML = `Found ${matchCount} product${matchCount !== 1 ? "s" : ""} matching "${query}"`
    searchStats.classList.add("show")
  }

  // Add search highlight effect
  setTimeout(() => {
    productCards.forEach((card) => {
      if (card.classList.contains("search-match")) {
        card.classList.add("search-highlight")
        setTimeout(() => {
          card.classList.remove("search-highlight")
        }, 600)
      }
    })
  }, 100)
}

// Fetch search suggestions from server
function fetchSearchSuggestions(query) {
  if (query.length < 2) return

  fetch(`api/search_suggestions.php?q=${encodeURIComponent(query)}`)
    .then((response) => response.json())
    .then((data) => {
      displaySearchSuggestions(data, query)
    })
    .catch((error) => {
      console.error("Search suggestions error:", error)
    })
}

// Display search suggestions
function displaySearchSuggestions(suggestions, query) {
  const suggestionsContainer = document.getElementById("search-suggestions")
  if (!suggestionsContainer) return

  if (suggestions.length === 0) {
    suggestionsContainer.innerHTML = `
            <div class="no-suggestions">
                <i class="fas fa-search"></i>
                <p>No suggestions found for "${query}"</p>
            </div>
        `
  } else {
    suggestionsContainer.innerHTML = suggestions
      .map(
        (item) => `
            <div class="suggestion-item" onclick="selectSearchSuggestion('${item.name}', ${item.id})">
                <img src="assets/images/products/${item.image}" alt="${item.name}" loading="lazy">
                <div class="suggestion-info">
                    <div class="suggestion-name">${highlightSearchTerm(item.name, query)}</div>
                    <div class="suggestion-category">${item.category.toUpperCase()}</div>
                </div>
                <div class="suggestion-price">Rp ${formatPrice(item.price)}</div>
            </div>
        `,
      )
      .join("")
  }

  showSuggestions()
}

// Highlight search terms in suggestions
function highlightSearchTerm(text, term) {
  const regex = new RegExp(`(${term})`, "gi")
  return text.replace(regex, '<span class="search-highlight">$1</span>')
}

// Show search suggestions
function showSuggestions() {
  const suggestionsContainer = document.getElementById("search-suggestions")
  if (suggestionsContainer) {
    suggestionsContainer.classList.add("show")
  }
}

// Hide search suggestions
function hideSuggestions() {
  const suggestionsContainer = document.getElementById("search-suggestions")
  if (suggestionsContainer) {
    suggestionsContainer.classList.remove("show")
  }
}

// Select search suggestion
function selectSearchSuggestion(productName, productId) {
  const searchInput = document.getElementById("search-input")
  if (searchInput) {
    searchInput.value = productName
  }

  hideSuggestions()

  // Scroll to product or show quick view
  const productCard = document.querySelector(`[data-id="${productId}"]`)
  if (productCard) {
    productCard.scrollIntoView({ behavior: "smooth", block: "center" })
    productCard.classList.add("search-highlight")
    setTimeout(() => {
      productCard.classList.remove("search-highlight")
    }, 2000)
  }
}

// Update selected suggestion for keyboard navigation
function updateSelectedSuggestion(suggestions, index) {
  suggestions.forEach((item, i) => {
    if (i === index) {
      item.classList.add("selected")
      item.scrollIntoView({ block: "nearest" })
    } else {
      item.classList.remove("selected")
    }
  })
}

// Perform full search (redirect with query)
function performFullSearch() {
  const searchInput = document.getElementById("search-input")
  const query = searchInput.value.trim()

  if (query) {
    const currentUrl = new URL(window.location)
    currentUrl.searchParams.set("search", query)
    window.location.href = currentUrl.toString()
  }
}

// Clear search results
function clearSearchResults() {
  const productCards = document.querySelectorAll(".product-card")
  const searchStats = document.getElementById("search-stats")

  productCards.forEach((card) => {
    card.style.display = "block"
    card.classList.remove("search-match", "search-hidden", "search-highlight")
  })

  if (searchStats) {
    searchStats.classList.remove("show")
  }

  isSearching = false
}

// Initialize product filters
function initializeProductFilters() {
  const filterOptions = document.querySelectorAll(".filter-option")
  const sortSelect = document.getElementById("sort-select")

  // Filter option click handlers
  filterOptions.forEach((option) => {
    option.addEventListener("click", function (e) {
      e.preventDefault()

      // Add loading effect
      this.style.opacity = "0.7"

      // Navigate to filtered URL
      setTimeout(() => {
        window.location.href = this.href
      }, 200)
    })
  })

  // Sort select change handler
  if (sortSelect) {
    sortSelect.addEventListener("change", () => {
      updateSort()
    })
  }
}

// Update sort function
function updateSort() {
  const sortSelect = document.getElementById("sort-select")
  const sortValue = sortSelect.value

  // Show loading state
  sortSelect.disabled = true
  sortSelect.style.opacity = "0.7"

  // Update URL with new sort parameter
  const urlParams = new URLSearchParams(window.location.search)
  urlParams.set("sort", sortValue)

  // Navigate to new URL
  window.location.href = "products.php?" + urlParams.toString()
}

// Initialize product animations
function initializeProductAnimations() {
  // Stagger animation for product cards
  const productCards = document.querySelectorAll(".product-card")
  productCards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`
    card.classList.add("fade-in")
  })

  // Intersection Observer for scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("animate-in")

        // Add special animation for product cards
        if (entry.target.classList.contains("product-card")) {
          setTimeout(() => {
            entry.target.style.transform = "translateY(0)"
            entry.target.style.opacity = "1"
          }, 100)
        }
      }
    })
  }, observerOptions)

  // Observe product cards for animation
  productCards.forEach((card) => {
    observer.observe(card)
  })
}

// Initialize product interactions
function initializeProductInteractions() {
  // Product card hover effects
  const productCards = document.querySelectorAll(".product-card")

  productCards.forEach((card) => {
    // Mouse enter effect
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-15px) scale(1.02)"

      // Add glow effect
      this.style.boxShadow = "0 20px 40px rgba(43, 62, 52, 0.3)"
    })

    // Mouse leave effect
    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)"
      this.style.boxShadow = ""
    })

    // Click effect for product image
    const productImage = card.querySelector(".product-image")
    if (productImage) {
      productImage.addEventListener("click", function () {
        // Add click animation
        this.style.transform = "scale(0.95)"
        setTimeout(() => {
          this.style.transform = "scale(1)"
        }, 150)
      })
    }
  })

  // Enhanced button interactions
  const buttons = document.querySelectorAll(".btn-add-cart, .btn-quick-view")
  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      // Create ripple effect
      createRippleEffect(e, this)
    })
  })
}

// Create ripple effect for buttons
function createRippleEffect(event, element) {
  const ripple = document.createElement("span")
  const rect = element.getBoundingClientRect()
  const size = Math.max(rect.width, rect.height)
  const x = event.clientX - rect.left - size / 2
  const y = event.clientY - rect.top - size / 2

  ripple.style.width = ripple.style.height = size + "px"
  ripple.style.left = x + "px"
  ripple.style.top = y + "px"
  ripple.classList.add("ripple")

  element.appendChild(ripple)

  setTimeout(() => {
    ripple.remove()
  }, 600)
}

// Enhanced product quick view
function quickViewProduct(productId) {
  // Show loading overlay
  showLoadingOverlay()

  // Fetch product details with enhanced error handling
  fetch(`api/get_product.php?id=${productId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.json()
    })
    .then((product) => {
      if (product.error) {
        throw new Error(product.error)
      }

      // Populate and show quick view modal
      populateQuickViewModal(product)
      showQuickViewModal()
    })
    .catch((error) => {
      console.error("Error fetching product:", error)
      alert("Failed to load product details")
    })
    .finally(() => {
      hideLoadingOverlay()
    })
}

// Show loading overlay
function showLoadingOverlay() {
  const overlay = document.createElement("div")
  overlay.id = "loading-overlay"
  overlay.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading product details...</p>
        </div>
    `
  overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(43, 62, 52, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    `

  document.body.appendChild(overlay)
}

// Hide loading overlay
function hideLoadingOverlay() {
  const overlay = document.getElementById("loading-overlay")
  if (overlay) {
    overlay.remove()
  }
}

// Advanced search filters
function initializeAdvancedFilters() {
  const priceRange = document.getElementById("price-range")
  const colorFilters = document.querySelectorAll(".color-filter")
  const sizeFilters = document.querySelectorAll(".size-filter")

  // Price range filter
  if (priceRange) {
    priceRange.addEventListener(
      "input",
      debounce(function () {
        filterProductsByPrice(this.value)
      }, 500),
    )
  }

  // Color filters
  colorFilters.forEach((filter) => {
    filter.addEventListener("click", function () {
      this.classList.toggle("active")
      updateColorFilters()
    })
  })

  // Size filters
  sizeFilters.forEach((filter) => {
    filter.addEventListener("click", function () {
      this.classList.toggle("active")
      updateSizeFilters()
    })
  })
}

// Filter products by price range
function filterProductsByPrice(maxPrice) {
  const productCards = document.querySelectorAll(".product-card")

  productCards.forEach((card) => {
    const priceElement = card.querySelector(".product-price")
    if (priceElement) {
      const price = Number.parseInt(priceElement.textContent.replace(/[^\d]/g, ""))

      if (price <= maxPrice) {
        card.style.display = "block"
        card.classList.add("price-match")
      } else {
        card.style.display = "none"
        card.classList.remove("price-match")
      }
    }
  })
}

// Update color filters
function updateColorFilters() {
  const activeColors = Array.from(document.querySelectorAll(".color-filter.active")).map(
    (filter) => filter.dataset.color,
  )

  if (activeColors.length === 0) {
    // Show all products if no color filter is active
    document.querySelectorAll(".product-card").forEach((card) => {
      card.classList.remove("color-hidden")
    })
    return
  }

  // Filter products by selected colors
  document.querySelectorAll(".product-card").forEach((card) => {
    const productColors = card.dataset.colors ? card.dataset.colors.split(",") : []
    const hasMatchingColor = activeColors.some((color) => productColors.includes(color))

    if (hasMatchingColor) {
      card.classList.remove("color-hidden")
    } else {
      card.classList.add("color-hidden")
    }
  })
}

// Update size filters
function updateSizeFilters() {
  const activeSizes = Array.from(document.querySelectorAll(".size-filter.active")).map((filter) => filter.dataset.size)

  if (activeSizes.length === 0) {
    // Show all products if no size filter is active
    document.querySelectorAll(".product-card").forEach((card) => {
      card.classList.remove("size-hidden")
    })
    return
  }

  // Filter products by selected sizes
  document.querySelectorAll(".product-card").forEach((card) => {
    const productSizes = card.dataset.sizes ? card.dataset.sizes.split(",") : []
    const hasMatchingSize = activeSizes.some((size) => productSizes.includes(size))

    if (hasMatchingSize) {
      card.classList.remove("size-hidden")
    } else {
      card.classList.add("size-hidden")
    }
  })
}

// Utility function for debouncing
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Format price utility
function formatPrice(price) {
  return new Intl.NumberFormat("id-ID").format(price)
}

// Hide suggestions when clicking outside
document.addEventListener("click", (event) => {
  const searchContainer = document.querySelector(".search-container")
  if (searchContainer && !searchContainer.contains(event.target)) {
    hideSuggestions()
  }
})

// Export functions for global access
window.productPage = {
  performLiveSearch,
  selectSearchSuggestion,
  updateSort,
  quickViewProduct,
  clearSearchResults,
}

// Inject CSS
const rippleCSS = `
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

.search-highlight {
    background: linear-gradient(120deg, var(--olive-drab) 0%, var(--moss-green) 100%);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
}

.product-card.search-highlight {
    animation: searchPulse 0.6s ease;
    border: 2px solid var(--olive-drab);
}

.suggestion-item.selected {
    background: var(--light-gray);
    border-left: 4px solid var(--olive-drab);
}

.loading-spinner {
    text-align: center;
    color: white;
}

.loading-spinner i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.loading-spinner p {
    font-size: 1.1rem;
    opacity: 0.9;
}
`

// Inject CSS
const style = document.createElement("style")
style.textContent = rippleCSS
document.head.appendChild(style)

// Declare functions used in quickViewProduct
function populateQuickViewModal(product) {
  // Implementation for populating the quick view modal
  console.log("Populating quick view modal with product:", product)
}

function showQuickViewModal() {
  // Implementation for showing the quick view modal
  console.log("Showing quick view modal")
}
