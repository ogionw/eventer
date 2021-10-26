<template>
  <form @submit.prevent="submit">
    <input type="text" placeholder="sku" v-model="sku">
    <input type="text" placeholder="quantity" v-model="quantity">
    <button type="submit">Create Product</button>
    <hr>
  </form>
</template>

<script>
import { mapMutations } from "vuex";
export default {
  data() {
    return {
      sku: "",
      quantity: ""
    };
  },
  methods: {
    ...mapMutations(["createProduct"]),
    submit() {
      this.createProduct({
        sku: this.sku,
        quantity: this.quantity,
        id: this.sku
      });
      this.sku = this.quantity = "";
    }
  }
};
</script>


<style scoped>
input {
  display: block;
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 2px;
  padding: 10px;
  margin-bottom: 10px;
}
</style>

