<template>
  <div id="app">
    <ProductForm/>
    <h1>{{productsCount}}</h1>
    <div class="product" v-for="product in validProducts" :key="product.sku">
      <h2>{{product.sku}}</h2>
      <p>{{product.quantity}}</p>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import ProductForm from "./components/ProductForm";
export default {
  name: "app",
  computed: mapGetters(["validProducts", "productsCount"]),
  methods: mapActions(["fetchProducts"]),
  components: { ProductForm },
  async mounted() {
    // this.$store.dispatch("fetchProducts");
    await this.fetchProducts();
  }
};
</script>

<style>
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin: 60px auto;
  width: 400px;
}

.product {
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-bottom: 1rem;
}
</style>
