<script setup lang="ts">
import { ref, computed } from "vue";

// Declare Post type
interface Post {
    id: number;
    title: string;
    category: string;
    content: string;
    author: string;
    date: string;
}

const posts = ref<Post[]>([]);
const searchQuery = ref("");
const isImporting = ref(false);
const isLoading = ref(false);

// Function that retrives from the app's API the currently stored posts
const fetchStoredPosts = async () => {
    // Set state to loading while waiting for the operation to finish
    isLoading.value = true;
    
    await fetch("/api")
        .then(res => res.json())
        .then(({ data }) => { posts.value = data })
        .catch(e => console.error("Failed to fetch posts: ", e))
        .finally(() => { isLoading.value = false }); // Change state to loaded;
};

// Function for invoking the app's import action
const triggerImport = async () => {
    // Set state to importing while waiting for the operation to finish
    isImporting.value = true;
    // Trigger import and retrieve posts once the import is successful
    await fetch("/api/import", { method: "POST" })
        .then(fetchStoredPosts)
        .catch(e => console.error("Failed to fetch posts: ", e))
        .finally(() => { isImporting.value = false }); // Change state to imported
};

// Initial attempt to get already stored posts
fetchStoredPosts();

// Filter function that matches the user's search text to posts' properties
const filteredPosts = computed(() => posts.value.filter(
    post => {
        const search = searchQuery.value.toLowerCase();
        return post.title.toLowerCase().includes(search)
            || post.category.toLowerCase().includes(search)
            || post.content.toLowerCase().includes(search)
            || post.author.toLowerCase().includes(search)
            || post.date.toLowerCase().includes(search);
    }))

</script>

<template>
    <div class="p-8">
        <h1 class="text-xl">Blog</h1>
        <p>Simple app built with Laravel and Vue for evaluation purposes.</p>
    </div>
    <div v-if="isLoading" class="p-8 pt-0">
        <p>Loading...</p>
    </div>
    <div v-if="!isLoading" class="p-8 pt-0">
        <!-- Only show filter input if the posts list is not empty -->
        <input v-if="posts.length > 0" v-model="searchQuery" type="text" placeholder="Filter..." class="text-black p-1 border-2 border-gray-400 rounded-sm"/>
        <!-- Alternatively, show a button that triggers the import -->
        <button v-if="posts.length < 1" @click="triggerImport" :disabled="isLoading" class="text-black p-1 border-2 border-gray-400 rounded-sm px-8">{{ isImporting ? "Loading..." : "Import posts" }}</button>
        <ul>
            <li v-for="post in filteredPosts" :key="post.id">
                <div class="mt-8 mb-4">
                    <h4>{{ post.title }}</h4> 
                    <p class="text-sm dark:text-[#ccc]">{{ post.category }} | @{{ post.author }}, {{ post.date }}</p>
                    <p class="text-xs dark:text-[#ccc]">{{ post.content }}</p>
                </div>
            </li>
            <!-- User feedback for the list being empty -->
            <li v-if="filteredPosts.length < 1">
                <div class="mt-8 mb-4">
                    <p class="text-sm dark:text-[#ccc]">There are no posts to show.</p>
                </div>
            </li>
        </ul>
    </div>
</template>
