# Alpine.js Best Practices - GSRC Lords Bonnensysteem

## Architecture Overview

The application now uses a modern Alpine.js architecture with:
- **Stores**: Global state management
- **Components**: Reusable, modular Alpine components
- **Build System**: Vite for bundling and hot module replacement

## Directory Structure

```
resources/
├── js/
│   ├── alpine/
│   │   ├── components/      # Alpine components
│   │   │   ├── members.js
│   │   │   ├── member-order-modal.js
│   │   │   ├── products.js
│   │   │   ├── groups.js
│   │   │   └── invoice.js
│   │   ├── stores/          # Global stores
│   │   │   ├── notifications.js
│   │   │   ├── app.js
│   │   │   └── index.js
│   │   └── utils/           # Utility functions (future)
│   └── app.js               # Main entry point
```

## Best Practices

### 1. **Use Alpine Stores for Shared State**

✅ **GOOD** - Use stores for global state:
```javascript
// In component
Alpine.store('notifications').success('Member added');
Alpine.store('app').startLoading();
```

❌ **BAD** - Duplicating state across components:
```javascript
// In each component
this.isLoading = true;
this.notifications = [];
```

**Available Stores:**
- `Alpine.store('notifications')` - Notification management
- `Alpine.store('app')` - Global app state (loading, user, modals)

### 2. **Avoid Direct DOM Manipulation**

✅ **GOOD** - Use Alpine reactivity:
```javascript
async loadModal(id) {
    const response = await http.get(`/product/${id}/edit`);
    this.modalContent = response.data;  // Alpine will update the DOM
}
```
```html
<div x-html="modalContent"></div>
```

❌ **BAD** - Direct DOM manipulation:
```javascript
document.getElementById('modal').innerHTML = response.data;
```

### 3. **Use Magic Properties**

**$refs** - Access elements by reference:
```javascript
// Component
this.$refs.addMemberForm.reset();
this.$refs.searchInput.focus();
```
```html
<!-- Template -->
<form x-ref="addMemberForm">
<input x-ref="searchInput">
```

**$nextTick** - Wait for DOM updates:
```javascript
this.members.push(newMember);
this.$nextTick(() => {
    this.$refs.searchInput.focus();
});
```

**$el** - Reference current element:
```javascript
const formData = new FormData(this.$el);
```

**$dispatch** - Emit custom events:
```javascript
this.$dispatch('order-created', { orderId: 123 });
```

**$watch** - React to data changes:
```javascript
init() {
    this.$watch('searchQuery', () => {
        this.performSearch();
    });
}
```

### 4. **Avoid Page Reloads**

✅ **GOOD** - Update state and dispatch events:
```javascript
async saveOrder() {
    const response = await http.post(url, formData);

    // Update parent component state via event
    this.$dispatch('order-created', response.data);

    // Close modal
    const modal = bootstrap.Modal.getInstance(this.$el.closest('.modal'));
    modal.hide();
}
```

❌ **BAD** - Full page reload:
```javascript
window.location.reload();
```

### 5. **Use Alpine's Event System**

✅ **GOOD** - Alpine events:
```javascript
// Emit
this.$dispatch('modal-loaded', { productId });

// Listen
<div @modal-loaded.window="handleModal($event.detail)">
```

❌ **BAD** - Custom DOM events:
```javascript
document.dispatchEvent(new CustomEvent('load-modal', { detail: id }));
```

### 6. **Component Structure**

Every component should follow this structure:

```javascript
export default (initialData = []) => ({
    // Data properties
    items: initialData,
    searchQuery: '',
    isLoading: false,

    // Initialize
    init() {
        // Setup watchers, event listeners
        this.$watch('searchQuery', () => this.performSearch());
    },

    // Computed properties (getters)
    get filteredItems() {
        return this.items.filter(/* ... */);
    },

    // Methods
    async loadData() {
        // ...
    },

    handleEvent(data) {
        // ...
    }
});
```

### 7. **Loading States**

Use the global app store for loading states:

```javascript
async addMember() {
    const store = Alpine.store('app');
    store.startLoading();

    try {
        // API call
    } catch (error) {
        // Error handling
    } finally {
        store.stopLoading();
    }
}
```

### 8. **Notifications**

Use the notifications store:

```javascript
// Success
Alpine.store('notifications').success('Item saved');

// Error
Alpine.store('notifications').error('Failed to save');

// Info
Alpine.store('notifications').info('Processing...');

// Warning
Alpine.store('notifications').warning('Check your input');

// Custom
Alpine.store('notifications').add({
    text: 'Custom message',
    type: 'info',
    duration: 5000
});
```

### 9. **Passing Data to Components**

From Blade template to component:

```html
<div x-data="membersManager(@json($members))" x-cloak>
```

Component receives data:
```javascript
export default (initialMembers = []) => ({
    members: initialMembers,
    // ...
});
```

### 10. **Form Handling**

✅ **GOOD** - Use x-ref and FormData:
```javascript
async addMember(event) {
    event.preventDefault();
    const formData = new FormData(this.$refs.addMemberForm);
    const response = await http.post(url, formData);
}
```
```html
<form x-ref="addMemberForm" @submit.prevent="addMember">
```

### 11. **Always Use x-cloak**

Prevent flash of unstyled content:

```html
<div x-data="component()" x-cloak>
    <!-- content -->
</div>
```

CSS (already in site.css):
```css
[x-cloak] {
    display: none !important;
}
```

### 12. **HTTP Requests**

Use the global `http` object (axios instance):

```javascript
// GET
const response = await http.get('/api/members');

// POST
const response = await http.post('/api/members', data);

// PUT
const response = await http.put(`/api/members/${id}`, data);

// DELETE
const response = await http.delete(`/api/members/${id}`);

// With FormData
const formData = new FormData(this.$refs.form);
const response = await http.post(url, formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
```

CSRF token is automatically included.

## Development Workflow

### Running Development Server

```bash
npm run dev
```

This starts Vite in development mode with hot module replacement.

### Building for Production

```bash
npm run build
```

This creates optimized production bundles in `public/build/`.

### Adding New Components

1. Create component file in `resources/js/alpine/components/`
2. Register in `resources/js/app.js`:
```javascript
import newComponent from './alpine/components/new-component.js';

Alpine.data('newComponent', newComponent);
```

3. Use in Blade template:
```html
<div x-data="newComponent()" x-cloak>
    <!-- content -->
</div>
```

### Adding New Stores

1. Create store file in `resources/js/alpine/stores/`
2. Export in `resources/js/alpine/stores/index.js`:
```javascript
import newStore from './new-store.js';

export default {
    // existing stores...
    newStore
};
```

3. Use in components:
```javascript
Alpine.store('newStore').someMethod();
```

## Common Patterns

### Modal Loading Pattern

```javascript
async loadModal(id) {
    const store = Alpine.store('app');
    store.startLoading();

    try {
        const response = await http.get(`/resource/${id}/edit`);
        this.modalContent = response.data;

        this.$nextTick(() => {
            this.$dispatch('modal-loaded', { id });
        });
    } catch (error) {
        Alpine.store('notifications').error('Failed to load');
    } finally {
        store.stopLoading();
    }
}
```

### Search/Filter Pattern

```javascript
export default (initialItems = []) => ({
    items: initialItems,
    searchQuery: '',

    get filteredItems() {
        if (!this.searchQuery) return this.items;
        const query = this.searchQuery.toLowerCase();
        return this.items.filter(item =>
            item.name.toLowerCase().includes(query)
        );
    }
});
```

### Add Item Pattern

```javascript
async addItem(event) {
    event.preventDefault();
    const store = Alpine.store('app');
    store.startLoading();

    try {
        const formData = new FormData(this.$refs.form);
        const response = await http.post(url, formData);

        if (response.data.success) {
            // Add to local state
            this.items.unshift(response.data.item);

            // Reset form
            this.$refs.form.reset();

            // Show notification
            Alpine.store('notifications').success(
                response.data.message || 'Item added'
            );

            // Focus back to search
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        }
    } catch (error) {
        Alpine.store('notifications').error(
            error.response?.data?.message || 'Error adding item'
        );
    } finally {
        store.stopLoading();
    }
}
```

## Anti-Patterns to Avoid

### ❌ Don't: Mix jQuery and Alpine
```javascript
// BAD
$('#element').hide();
$('#form').submit();
```

Use Alpine directives instead.

### ❌ Don't: Pollute Global Scope
```javascript
// BAD
window.myFunction = () => {};
```

Keep logic in components and stores.

### ❌ Don't: Use Inline Event Handlers
```html
<!-- BAD -->
<button onclick="doSomething()">Click</button>
```

Use Alpine's `@click`:
```html
<!-- GOOD -->
<button @click="doSomething()">Click</button>
```

### ❌ Don't: Forget Error Handling
```javascript
// BAD
async loadData() {
    const response = await http.get('/api/data');
    this.data = response.data;
}
```

Always use try/catch:
```javascript
// GOOD
async loadData() {
    try {
        const response = await http.get('/api/data');
        this.data = response.data;
    } catch (error) {
        Alpine.store('notifications').error('Failed to load');
    }
}
```

## Testing

When testing Alpine components:

1. Ensure Alpine is initialized
2. Check that stores are registered
3. Test computed properties
4. Test async methods with proper mocking
5. Verify event dispatching

## Performance Tips

1. **Use Computed Properties** for derived state instead of methods
2. **Debounce Search** for expensive operations
3. **Lazy Load** modals and heavy components
4. **Limit Watchers** - only watch what's necessary
5. **Use x-show vs x-if** appropriately:
   - `x-show` for frequently toggled elements (keeps in DOM)
   - `x-if` for rarely shown elements (removes from DOM)

## Resources

- [Alpine.js Documentation](https://alpinejs.dev/)
- [Alpine.js GitHub](https://github.com/alpinejs/alpine)
- [Vite Documentation](https://vitejs.dev/)
- [Axios Documentation](https://axios-http.com/)
