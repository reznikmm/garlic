
with Ada.Streams;
with AWS.Messages;
with AWS.MIME;
with AWS.Parameters;
with AWS.Session;

function HTTP_Callback (Request : AWS.Status.Data) return AWS.Response.Data is
   URI        : constant String := AWS.Status.URI (Request);
begin
   if URI = "/index.html" then
      return AWS.Response.File
        (Content_Type => AWS.MIME.Text_HTML,
         Filename     => "index.html");
   elsif URI = "/main.wasm" then
      return AWS.Response.File
        (Content_Type => "application/wasm",
         Filename     => "main.wasm");
   elsif URI = "/adawebpack.mjs" then
      return AWS.Response.File
        (Content_Type => AWS.MIME.Text_Javascript,
         Filename     => "adawebpack.mjs");
   else
      return AWS.Response.Acknowledge (AWS.Messages.S404);
   end if;
end HTTP_Callback;
