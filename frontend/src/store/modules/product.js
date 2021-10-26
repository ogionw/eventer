export default {
  actions: {
    async fetchProducts({ commit, getters, dispatch }, limit = 3) {
      const res = await fetch(
        'https://127.0.0.1:8000/products?_limit=' + limit
      )
      const products = await res.json()

      dispatch('sayHello')

      commit('updateProducts', products)
    },
    sayHello() {}
  },
  mutations: {
    updateProducts(state, products) {
      state.products = products
    },
    createProduct(state, newProduct) {
      state.products.unshift(newProduct)
    }
  },
  state: {
    products: []
  },
  getters: {
    validProducts(state) {
      return state.products.filter(p => {
        return p.sku && p.quantity
      })
    },
    allProducts(state) {
      return state.products
    },
    productsCount(state, getters) {
      return getters.validProducts.length
    }
  }
}
