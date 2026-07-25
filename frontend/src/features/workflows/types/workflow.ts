export interface Workflow {
  id: number;
  workspace_id: number;
  name: string;
  description?: string;
  is_template: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
  stages?: WorkflowStage[];
}

export interface WorkflowStage {
  id: number;
  workflow_id: number;
  name: string;
  order: number;
  color?: string;
  created_at: string;
  updated_at: string;
  tasks?: any[];
}

export interface CreateWorkflowRequest {
  name: string;
  description?: string;
  is_template?: boolean;
  stages?: Omit<WorkflowStage, 'id' | 'workflow_id' | 'created_at' | 'updated_at'>[];
}

export interface UpdateWorkflowRequest {
  name?: string;
  description?: string;
  is_template?: boolean;
}