/**
 * Workflow Preview JavaScript Module
 * Handles dynamic workflow preview generation and display
 */

class WorkflowPreview {
    constructor() {
        this.previewContainer = null;
        this.loadingState = false;
        this.currentPreviewData = null;
        
        this.init();
    }

    init() {
        // Auto-initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupEventListeners());
        } else {
            this.setupEventListeners();
        }
    }

    setupEventListeners() {
        // Listen for form changes to trigger preview updates
        document.addEventListener('change', (e) => {
            if (this.shouldTriggerPreview(e.target)) {
                this.handleFormChange(e);
            }
        });

        // Listen for preview button clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="preview-workflow"]')) {
                e.preventDefault();
                this.showPreviewModal();
            }
            
            if (e.target.matches('[data-action="refresh-preview"]')) {
                e.preventDefault();
                this.refreshPreview();
            }
        });

        // Setup quick preview on department selection
        const departmentSelect = document.querySelector('#iom_to_department_name_display');
        if (departmentSelect) {
            departmentSelect.addEventListener('input', () => {
                // Debounce the preview generation
                clearTimeout(this.departmentTimeout);
                this.departmentTimeout = setTimeout(() => {
                    this.generateQuickPreview();
                }, 800);
            });
        }
    }

    shouldTriggerPreview(element) {
        const triggerSelectors = [
            'select[name="request_type"]',
            'input[name="iom_to_department_name_display"]',
            'select[name="iom_to_department_id"]',
            'input[name="leave_type"]'
        ];
        
        return triggerSelectors.some(selector => element.matches(selector));
    }

    async handleFormChange(event) {
        // Debounce rapid changes
        clearTimeout(this.changeTimeout);
        this.changeTimeout = setTimeout(() => {
            this.generateQuickPreview();
        }, 500);
    }

    async generateQuickPreview() {
        const formData = this.collectFormData();
        
        if (!this.isFormReadyForPreview(formData)) {
            this.clearPreview();
            return;
        }

        try {
            this.showLoadingState();
            
            const response = await fetch('/workflow/preview/quick', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            
            if (result.success) {
                this.displayQuickPreview(result.data);
            } else {
                this.showPreviewError(result.message);
            }
            
        } catch (error) {
            this.showPreviewError('Unable to generate preview');
        } finally {
            this.hideLoadingState();
        }
    }

    async generateFullPreview() {
        const formData = this.collectFormData();
        
        if (!formData) {
            throw new Error('Form data is incomplete');
        }
        
        try {
            this.showLoadingState();
            
            const response = await fetch('/workflow/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned invalid response format');
            }

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            
            if (result.success) {
                this.currentPreviewData = result.data;
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to generate preview');
            }
        } catch (error) {
            console.error('Workflow preview error:', error);
            throw error;
        } finally {
            this.hideLoadingState();
        }
    }

    collectFormData() {
        const data = {};
        
        // Get the form type from the select element
        const requestTypeSelect = document.getElementById('request_type');
        const formType = requestTypeSelect ? requestTypeSelect.value : '';
        
        // Map form fields to expected API format
        data.form_type = formType;
        
        if (formType === 'IOM') {
            // Get department ID from hidden field or visible field
            const toDeptId = document.getElementById('to_department_id');
            const iomToDeptId = document.getElementById('iom_to_department_id');
            
            let deptId = '';
            if (toDeptId && toDeptId.value) {
                deptId = toDeptId.value;
            } else if (iomToDeptId && iomToDeptId.value) {
                deptId = iomToDeptId.value;
            }
            
            // Only proceed if we have a valid department ID
            if (deptId && deptId !== '' && deptId !== '0') {
                data.to_department_id = deptId;
            } else {
                // Don't submit without department ID
                return null;
            }
            
            // Get IOM specific fields
            const iomRe = document.getElementById('iom_re');
            const iomDescription = document.getElementById('iom_description');
            
            data.title = iomRe ? iomRe.value : '';
            data.description = iomDescription ? iomDescription.value : '';
            
        } else if (formType === 'Leave') {
            // Get leave type from radio buttons
            const leaveTypeInputs = document.querySelectorAll('input[name="leave_type"]:checked');
            if (leaveTypeInputs.length > 0) {
                data.leave_type = leaveTypeInputs[0].value;
            } else {
                // Don't submit without leave type
                return null;
            }
            
            // Get leave description
            const leaveDescription = document.getElementById('leave_description');
            data.description = leaveDescription ? leaveDescription.value : '';
        } else {
            // No form type selected
            return null;
        }
        
        return data;
    }

    isFormReadyForPreview(formData) {
        const hasFormType = formData.form_type && formData.form_type !== '';
        
        if (formData.form_type === 'IOM') {
            return hasFormType && formData.to_department_id && formData.to_department_id !== '';
        }
        
        if (formData.form_type === 'Leave') {
            return hasFormType && formData.leave_type && formData.leave_type !== '';
        }
        
        return false;
    }

    displayQuickPreview(previewData) {
        const container = this.getPreviewContainer();
        
        container.innerHTML = `
            <div class="workflow-preview-container bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 rounded-lg p-4 mb-6 shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h4 class="text-base font-semibold text-gray-900 mb-3 flex items-center">
                            Workflow Preview
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${previewData.workflow_type || previewData.form_type}
                            </span>
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-4">
                            <div class="bg-white rounded-md p-3 text-center border border-blue-100 workflow-step-card">
                                <div class="text-lg font-bold text-blue-600">${previewData.total_steps}</div>
                                <div class="text-xs text-gray-600">Total Steps</div>
                            </div>
                            <div class="bg-white rounded-md p-3 text-center border border-green-100 workflow-step-card">
                                <div class="text-lg font-bold text-green-600">${previewData.estimated_days === 0 ? '< 1' : previewData.estimated_days}</div>
                                <div class="text-xs text-gray-600">Business Days</div>
                            </div>
                            <div class="bg-white rounded-md p-3 text-center border border-purple-100 md:col-span-1 col-span-2 workflow-step-card">
                                <button type="button" data-action="preview-workflow" class="text-purple-600 font-semibold hover:text-purple-700 transition-colors text-sm">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </button>
                            </div>
                        </div>
                        ${previewData.requires_job_order ? 
                            '<div class="flex items-center text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2"><svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>Job Order will be automatically created</div>' : 
                            ''
                        }
                    </div>
                </div>
            </div>
        `;
    }

    async showPreviewModal() {
        try {
            const previewData = await this.generateFullPreview();
            
            // Create and show modal
            const modal = this.createPreviewModal(previewData);
            document.body.appendChild(modal);
            
            // Animate modal in
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            }, 10);
            
        } catch (error) {
            alert('Unable to generate preview: ' + error.message);
        }
    }

    createPreviewModal(previewData) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto opacity-0 transition-opacity duration-300';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="this.closest('.fixed').remove()"></div>
                
                <div class="modal-content inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full scale-95">
                    <!-- Header with Summary Cards -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-white">Complete Workflow Preview</h3>
                            <button type="button" onclick="this.closest('.fixed').remove()" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Summary Cards -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                <div class="text-2xl font-bold text-white">${previewData.total_steps || previewData.workflow_steps.length}</div>
                                <div class="text-sm text-blue-100">Total Steps</div>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                <div class="text-2xl font-bold text-white">${previewData.estimated_completion_days || 5}</div>
                                <div class="text-sm text-blue-100">Business Days</div>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-lg p-3">
                                <div class="text-2xl font-bold text-white">${previewData.form_type || 'N/A'}</div>
                                <div class="text-sm text-blue-100">Request Type</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Workflow Steps -->
                    <div class="bg-white px-6 py-6">
                        <div class="workflow-steps">
                            ${this.renderFullPreview(previewData)}
                        </div>
                    </div>
                    
                    <!-- Footer Actions -->
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="this.closest('.fixed').remove(); document.querySelector('#submitButton')?.click();" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-3 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors sm:ml-3 sm:w-auto sm:text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Proceed with Submission
                        </button>
                        <button type="button" onclick="this.closest('.fixed').remove()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    renderFullPreview(previewData) {
        let stepsHtml = '';
        
        previewData.workflow_steps.forEach((step, index) => {
            const isLast = index === previewData.workflow_steps.length - 1;
            const stepIcon = this.getStepIcon(step.action);
            
            stepsHtml += `
                <div class="relative flex items-start space-x-4 ${!isLast ? 'pb-6' : ''}">
                    <!-- Timeline Line -->
                    ${!isLast ? '<div class="absolute left-6 top-12 w-0.5 h-full bg-gray-200"></div>' : ''}
                    
                    <!-- Step Number/Status -->
                    <div class="flex-shrink-0 relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-md ${
                            step.status === 'current' ? 'bg-blue-500 text-white ring-4 ring-blue-100' : 
                            step.status === 'completed' ? 'bg-green-500 text-white ring-4 ring-green-100' : 
                            'bg-white border-2 border-gray-300 text-gray-600'
                        }">
                            ${step.status === 'completed' ? 
                                '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' :
                                step.step_number
                            }
                        </div>
                    </div>
                    
                    <!-- Step Content -->
                    <div class="flex-1 min-w-0">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-gray-300 transition-colors workflow-step-card">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                        ${stepIcon}
                                        <span class="ml-2">${step.title}</span>
                                    </h4>
                                    <p class="text-sm text-gray-600 mt-1">${step.description}</p>
                                </div>
                                <span class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                    step.status === 'current' ? 'bg-blue-100 text-blue-800' :
                                    step.status === 'completed' ? 'bg-green-100 text-green-800' :
                                    'bg-gray-100 text-gray-800'
                                }">
                                    ${step.estimated_duration}
                                </span>
                            </div>
                            
                            <!-- Actor Information -->
                            <div class="mt-3 flex items-center space-x-6 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-medium">${step.actor}</span>
                                    ${step.actor_position ? `<span class="ml-1 text-gray-500">(${step.actor_position})</span>` : ''}
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-6a1 1 0 00-1-1H9a1 1 0 00-1 1v6a1 1 0 01-1 1H4a1 1 0 110-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span>${step.department}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        return stepsHtml;
    }

    getStepIcon(action) {
        const icons = {
            'Submit': '<svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'Review': '<svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>',
            'Approve': '<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
            'Forward': '<svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>',
            'Process': '<svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>',
            'Complete': '<svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
        };
        
        return icons[action] || icons['Process'];
    }

    getPreviewContainer() {
        if (!this.previewContainer) {
            this.previewContainer = document.querySelector('#workflow-preview-container');
            
            if (!this.previewContainer) {
                // Create container if it doesn't exist
                this.previewContainer = document.createElement('div');
                this.previewContainer.id = 'workflow-preview-container';
                
                // Insert after form or at a sensible location
                const form = document.querySelector('#request-form') || document.querySelector('form');
                if (form) {
                    form.parentNode.insertBefore(this.previewContainer, form.nextSibling);
                }
            }
        }
        
        return this.previewContainer;
    }

    showLoadingState() {
        this.loadingState = true;
        const container = this.getPreviewContainer();
        container.innerHTML = `
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="animate-spin w-5 h-5 text-gray-600 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-700">Generating workflow preview...</span>
                </div>
            </div>
        `;
    }

    hideLoadingState() {
        this.loadingState = false;
    }

    showPreviewError(message) {
        const container = this.getPreviewContainer();
        container.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-red-700">${message}</span>
                </div>
            </div>
        `;
    }

    clearPreview() {
        const container = this.getPreviewContainer();
        container.innerHTML = '';
    }

    refreshPreview() {
        this.clearPreview();
        this.generateQuickPreview();
    }
}

// Initialize when DOM is ready
const workflowPreview = new WorkflowPreview();
