<template>
  <div id="app">
    <ProductForm/>
    <h1>{{productsCount}}</h1>
    <Product />
  </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import ProductForm from "./components/ProductForm";
import Product from "./components/Product";
export default {
  name: "app",
  computed: mapGetters(["productsCount"]),
  methods: mapActions(["fetchProducts"]),
  components: { ProductForm, Product },
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
