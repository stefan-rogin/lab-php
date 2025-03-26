<script setup lang="ts">
import { ref, computed } from "vue";

const posts = ref([]);
const searchQuery = ref("");

fetch("/api")
    .then(res => res.json())
    .then(data => { posts.value = data })
    .catch(e => console.error("Failed to fetch posts: ", e));

const filteredPosts = computed(() => posts.value.filter(post => post.title.toLowerCase().includes(searchQuery.value.toLowerCase())))

</script>

<template>
    <div class="p-6">
        <input v-model="searchQuery" type="text" placeholder="Filter..." class="text-black p-1"/>
        <ul>
            <li v-for="post in filteredPosts" :key="post.id">{{ post.title }}</li>
        </ul>
    </div>
</template>
