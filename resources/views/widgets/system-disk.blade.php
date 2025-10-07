<div 
    x-data="widgetData('{{ $widget->key ?? 'system.disk' }}')" 
    x-init="init()"
    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
>
    <div class="p-2 text-gray-900 dark:text-gray-100">
        <div class="flex items-center justify-between mb-0.5">
            <h3 class="text-base font-semibold">💿 Diskplass</h3>
            <span class="text-xs text-gray-500" x-text="loading ? 'Laster...' : 'Oppdatert'"></span>
        </div>

        <template x-if="error">
            <div class="text-red-500 text-xs" x-text="error"></div>
        </template>

        <template x-if="!error && data">
            <div class="space-y-0.5">
                <!-- Filesystems -->
                <template x-for="fs in data.filesystems" :key="fs.mount_point">
                    <div class="pb-3 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between mb-1">
                            <div class="text-xs font-medium" x-text="fs.mount_point"></div>
                            <div class="text-xs font-semibold" x-text="fs.use_percent + '%'"></div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full transition-all duration-500"
                                :class="{
                                    'bg-green-600': fs.use_percent < 70,
                                    'bg-yellow-600': fs.use_percent >= 70 && fs.use_percent < 90,
                                    'bg-red-600': fs.use_percent >= 90
                                }"
                                :style="`width: ${fs.use_percent}%`"
                            ></div>
                        </div>
                        <div class="flex items-center justify-between mt-1 text-xs text-gray-500">
                            <span>
                                <span x-text="fs.formatted.used"></span> brukt
                            </span>
                            <span>
                                <span x-text="fs.formatted.available"></span> ledig av <span x-text="fs.formatted.size"></span>
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5" x-text="fs.filesystem"></div>
                    </div>
                </template>

                <!-- Inode info (optional, collapsed by default) -->
                <details class="text-xs" x-show="data.inodes && Object.keys(data.inodes).length > 0">
                    <summary class="cursor-pointer text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Inode-bruk (avansert)
                    </summary>
                    <div class="mt-0.5 space-y-0.5 pl-4">
                        <template x-for="(inode, mountPoint) in data.inodes" :key="mountPoint">
                            <div class="text-xs">
                                <div class="font-medium" x-text="mountPoint"></div>
                                <div class="text-gray-500">
                                    <span x-text="inode.used.toLocaleString()"></span> / 
                                    <span x-text="inode.total.toLocaleString()"></span> inodes
                                    (<span x-text="inode.use_percent + '%'"></span>)
                                </div>
                            </div>
                        </template>
                    </div>
                </details>

                <!-- Timestamp -->
                <div class="text-xs text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
                    Sist oppdatert: <span x-text="formatDateTime(data.timestamp)"></span>
                </div>
            </div>
        </template>
    </div>
</div>
