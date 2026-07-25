import { Link } from "react-router-dom";
import { RegisterForm } from "./RegisterForm";

export function RegisterPage() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-neutral-50 px-16 py-32">
      <div className="w-full max-w-md">
        <div className="mb-32 text-center">
          <h1 className="text-h3 font-bold text-neutral-900">YFlow</h1>
          <p className="mt-8 text-body text-neutral-600">
            Agency Operating System
          </p>
        </div>

        <div className="card">
          <h2 className="mb-24 text-center text-h4 font-semibold">
            Create Account
          </h2>
          <RegisterForm />

          <div className="mt-24 text-center">
            <p className="text-sm text-neutral-600">
              Already have an account?{" "}
              <Link
                to="/login"
                className="font-medium text-primary-600 hover:text-primary-700"
              >
                Sign in
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
