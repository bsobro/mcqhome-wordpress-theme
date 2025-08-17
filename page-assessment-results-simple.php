<?php
/**
 * Simple Assessment Results Page Template
 * Temporary replacement for the broken assessment results page
 *
 * @package MCQHome
 * @since 1.0.0
 */

get_header(); ?>

<div class="assessment-results-page min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Assessment Results</h1>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Assessment System Temporarily Unavailable</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>The assessment results system is currently being updated. Please check back later or contact support if you need immediate access to your results.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="<?php echo home_url('/dashboard/'); ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors inline-block">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>