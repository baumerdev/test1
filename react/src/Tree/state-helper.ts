import { DataNode } from "./TreeState";

export const recursivelyRemoveFolder = (
  folders: DataNode[],
  folderId: number
): DataNode[] => {
  return [...folders]
    .map((folder) => {
      if (folder.id === folderId) {
        return null;
      }

      return {
        ...folder,
        children: recursivelyRemoveFolder(folder.children, folderId),
      };
    })
    .filter((folder) => folder !== null) as DataNode[];
};

export const findFolderInTree = (
  folders: DataNode[],
  folderId: number
): DataNode | null => {
  for (const folder of folders) {
    if (folder.id === folderId) {
      return folder;
    }

    const foundFolder = findFolderInTree(folder.children, folderId);

    if (foundFolder) {
      return foundFolder;
    }
  }

  return null;
};

export const findHighestFolderId = (folders: DataNode[]): number => {
  let highestId = 0;

  for (const folder of folders) {
    if (folder.id > highestId) {
      highestId = folder.id;
    }

    const highestChildId = findHighestFolderId(folder.children);

    if (highestChildId > highestId) {
      highestId = highestChildId;
    }
  }

  return highestId;
};

export const addFolderToParentFolder = (
  folders: DataNode[],
  newFolderName: string,
  parentFolderId?: number
): DataNode[] => {
  const clonedFolders = JSON.parse(JSON.stringify(folders));
  const parentFolder = parentFolderId
    ? findFolderInTree(clonedFolders, parentFolderId)
    : null;

  const newFolder = {
    id: findHighestFolderId(clonedFolders) + 1,
    name: newFolderName,
    children: [],
  };

  if (parentFolderId && parentFolder) {
    parentFolder.children.push(newFolder);
  }

  if (!parentFolderId) {
    clonedFolders.push(newFolder);
  }

  return clonedFolders;
};
