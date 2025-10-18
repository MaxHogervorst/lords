/**
 * Modals Store
 * Manages modal data state globally using data binding instead of HTML injection
 */
export default {
    // Order modal data
    orderModal: {
        type: null, // 'member' or 'group'
        entity: null,
        products: [],
        orders: [],
        orderTotals: [],
        members: [], // For group members tab
        groupMembers: [], // Current group members
        activeTab: 'orders',
        currentMonth: null
    },

    // Edit modal data
    editModal: {
        type: null, // 'member', 'group', or 'product'
        entity: null
    },

    /**
     * Set order modal data for member
     */
    setMemberOrderData(data) {
        this.orderModal.type = 'member';
        this.orderModal.entity = data.member;
        this.orderModal.products = data.products || [];
        this.orderModal.orders = data.orders || [];
        this.orderModal.orderTotals = data.orderTotals || [];
        this.orderModal.currentMonth = data.currentMonth;
        this.orderModal.activeTab = 'orders';
    },

    /**
     * Set order modal data for group
     */
    setGroupOrderData(data) {
        this.orderModal.type = 'group';
        this.orderModal.entity = data.group;
        this.orderModal.products = data.products || [];
        this.orderModal.orders = data.orders || [];
        this.orderModal.orderTotals = data.orderTotals || [];
        this.orderModal.members = data.members || [];
        this.orderModal.groupMembers = data.groupMembers || [];
        this.orderModal.currentMonth = data.currentMonth;
        this.orderModal.activeTab = 'orders';
    },

    /**
     * Set edit modal data for member
     */
    setMemberEditData(member) {
        this.editModal.type = 'member';
        this.editModal.entity = member;
    },

    /**
     * Set edit modal data for group
     */
    setGroupEditData(group) {
        this.editModal.type = 'group';
        this.editModal.entity = group;
    },

    /**
     * Set edit modal data for product
     */
    setProductEditData(product) {
        this.editModal.type = 'product';
        this.editModal.entity = product;
    },

    /**
     * Clear all modal data
     */
    clear() {
        this.orderModal = {
            type: null,
            entity: null,
            products: [],
            orders: [],
            orderTotals: [],
            members: [],
            groupMembers: [],
            activeTab: 'orders',
            currentMonth: null
        };
        this.editModal = {
            type: null,
            entity: null
        };
    }
};
