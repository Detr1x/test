<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить запись</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.5/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md" x-data="recordForm()">
        <h2 class="text-2xl font-bold mb-4">Добавить запись</h2>

        <form @submit.prevent="submitForm">
            <!-- SNUM -->
            <label class="block mb-2">SNUM:</label>
            <input type="number" x-model="form.snum" class="w-full p-2 border rounded mb-3">

            <!-- SOURCE -->
            <label class="block mb-2">Source:</label>
            <select x-model="form.source" class="w-full p-2 border rounded mb-3">
                <option value="main_header">Main Header</option>
                <option value="header">Header</option>
                <option value="sub_header">Sub Header</option>
                <option value="sub_sub_header">Sub Sub Header</option>
            </select>

            <!-- UNIT -->
            <label class="block mb-2">Unit:</label>
            <input type="text" x-model="form.unit" class="w-full p-2 border rounded mb-3">

            <!-- AMOUNT -->
            <label class="block mb-2">Amount:</label>
            <input type="number" x-model="form.amount" class="w-full p-2 border rounded mb-3">

            <!-- ATTRIBUTES -->
            <label class="block mb-2">Атрибуты:</label>
            <template x-for="(attr, index) in form.attributes" :key="index">
                <div class="flex space-x-2 mb-2">
                    <input type="text" placeholder="Key" x-model="attr.key" class="w-1/2 p-2 border rounded">
                    <input type="text" placeholder="Value" x-model="attr.value" class="w-1/2 p-2 border rounded">
                    <button type="button" @click="removeAttribute(index)" class="bg-red-500 text-white px-3 py-2 rounded">X</button>
                </div>
            </template>
            <button type="button" @click="addAttribute" class="bg-blue-500 text-white px-3 py-2 rounded mb-3">+ Добавить атрибут</button>

            <!-- SUBMIT -->
            <button type="submit" class="w-full bg-green-500 text-white p-2 rounded">Сохранить</button>
        </form>

        <!-- ОТВЕТ -->
        <div x-show="message" class="mt-4 p-3 bg-gray-200 rounded">
            <p x-text="message"></p>
        </div>
    </div>

    <script>
        function recordForm() {
            return {
                form: {
                    snum: '',
                    source: 'main_header',
                    unit: '',
                    amount: '',
                    attributes: []
                },
                message: '',
                addAttribute() {
                    this.form.attributes.push({ key: '', value: '' });
                },
                removeAttribute(index) {
                    this.form.attributes.splice(index, 1);
                },
                async submitForm() {
                    const response = await fetch("{{ route('records.store') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(this.form)
                    });

                    if (response.ok) {
                        this.message = "Запись успешно сохранена!";
                        this.form = { snum: '', source: 'main_header', unit: '', amount: '', attributes: [] };
                    } else {
                        this.message = "Ошибка при сохранении.";
                    }
                }
            };
        }
    </script>

</body>
</html>
