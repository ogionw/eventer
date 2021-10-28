<template>
    <div>
    <div class="product" v-for="product in validProducts" :key="product.sku">
        <h2>{{product.sku}}</h2>
        <p>Added: {{product.firstAddedAt}}</p>
        <p>Last Update: {{product.lastUpdatedAt}}</p>
        <p>quantity: {{product.quantity}}</p>
        <select name="event" v-on:change="showForm($event)">
            <option value=""></option>
            <option value="receiveProduct">Receive Product</option>
            <option value="shipProduct">Ship Product</option>
            <option value="adjustProduct">Adjust Product</option>
        </select>
    </div>
    </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";

export default {
    computed: mapGetters(["validProducts"]),
    methods: {
        ...mapActions(["fetchProducts"]),
        showForm(event) {
            if(event.target.value === 'receiveProduct'){

            }
        }
    },
}
</script>

