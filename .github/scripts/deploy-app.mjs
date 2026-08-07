import { deploy } from "@samkirkland/ftp-deploy";

const { FTP_SERVER, FTP_USERNAME, FTP_PASSWORD } = process.env;

if (!FTP_SERVER || !FTP_USERNAME || !FTP_PASSWORD) {
  console.error("❌ Hiányzó FTP_SERVER / FTP_USERNAME / FTP_PASSWORD környezeti változó.");
  process.exit(1);
}

console.log("🚚 Deploy App started");
await deploy({
  server: FTP_SERVER,
  username: FTP_USERNAME,
  password: FTP_PASSWORD,
  "local-dir": "./app/",
  "server-dir": "./app/",
  "log-level": "minimal",
});
console.log("🚀 Deploy App done!");
