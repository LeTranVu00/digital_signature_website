<aside class="w-64 bg-slate-900 text-white min-h-screen">

    <div class="p-6 text-2xl font-bold border-b border-slate-700">
        Digital Signature
    </div>

    <nav class="mt-6">

        <a href="/dashboard"
           class="block px-6 py-3 hover:bg-slate-800">
            Dashboard
        </a>

        <a href="/admin/categories"
           class="block px-6 py-3 hover:bg-slate-800">
            Categories
        </a>

        <a
            href="{{ route('admin.posts.index') }}"
            class="block px-6 py-3 transition hover:bg-slate-800"
        >
            Posts
        </a>

        <a href="#"
           class="block px-6 py-3 hover:bg-slate-800">
            Users
        </a>

        <a href="#"
           class="block px-6 py-3 hover:bg-slate-800">
            Comments
        </a>

    </nav>

</aside>