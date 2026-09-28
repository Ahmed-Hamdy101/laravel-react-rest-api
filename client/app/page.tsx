import Image from "next/image";
import Dashboard from "@/app/components/dashboard";

export default function Home() {
  return (
    <div className="flex flex-col  items-center justify-center bg-zinc-50 font-sans dark:bg-black">
          <Dashboard/>
    </div>
  );
}
